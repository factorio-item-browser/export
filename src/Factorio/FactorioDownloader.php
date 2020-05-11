<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Factorio;

use FactorioItemBrowser\Export\Console\Console;
use FactorioItemBrowser\Export\Process\DownloadProcess;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

/**
 * The class able to download the Factorio game to the project.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class FactorioDownloader
{
    /**
     * The URL to download the game.
     */
    protected const DOWNLOAD_URL = 'https://www.factorio.com/get-download/%s/%s/linux64';

    /**
     * The name of the headless variant of Factorio.
     */
    protected const VARIANT_HEADLESS = 'headless';

    /**
     * The name of the full variant of Factorio.
     */
    protected const VARIANT_FULL = 'alpha';

    /**
     * The console.
     * @var Console
     */
    protected $console;

    /**
     * The file system.
     * @var Filesystem
     */
    protected $fileSystem;

    /**
     * The directory to write the downloaded factorio into.
     * @var string
     */
    protected $factorioDirectory;

    /**
     * The username required to download the Factorio game.
     * @var string
     */
    protected $factorioDownloadUsername;

    /**
     * The token required to download the Factorio game.
     * @var string
     */
    protected $factorioDownloadToken;

    /**
     * The temporary directory to use.
     * @var string
     */
    protected $tempDirectory;

    /**
     * FactorioDownloader constructor.
     * @param Console $console
     * @param Filesystem $fileSystem
     * @param string $factorioDirectory
     * @param string $factorioDownloadUsername
     * @param string $factorioDownloadToken
     * @param string $tempDirectory
     */
    public function __construct(
        Console $console,
        Filesystem $fileSystem,
        string $factorioDirectory,
        string $factorioDownloadUsername,
        string $factorioDownloadToken,
        string $tempDirectory
    ) {
        $this->console = $console;
        $this->fileSystem = $fileSystem;
        $this->factorioDirectory = $factorioDirectory;
        $this->factorioDownloadUsername = $factorioDownloadUsername;
        $this->factorioDownloadToken = $factorioDownloadToken;
        $this->tempDirectory = $tempDirectory;
    }

    /**
     * Downloads the specified version of Factorio, replacing the previously installed one.
     * @param string $version
     */
    public function download(string $version): void
    {
        $this->console->writeHeadline("Downloading and installing Factorio version {$version}");

        $headlessArchive = $this->tempDirectory . '/headless.tar.xz';
        $headlessDirectory = $this->tempDirectory . '/headless';
        $fullArchive = $this->tempDirectory . '/full.tar.xz';
        $fullDirectory = $this->tempDirectory . '/full';

        $this->console->writeAction('Starting download of headless variant of Factorio');
        $headlessProcess = $this->createDownloadProcess(self::VARIANT_HEADLESS, $version, $headlessArchive);
        $headlessProcess->start();

        $this->console->writeAction('Starting download of full variant of Factorio');
        $fullProcess = $this->createDownloadProcess(self::VARIANT_FULL, $version, $fullArchive);
        $fullProcess->start();

        $this->console->writeAction('Waiting for headless download to finish.');
        $headlessProcess->wait();

        $this->console->writeAction('Extracting files of headless variant of Factorio');
        $this->createExtractArchiveProcess($headlessArchive, $headlessDirectory)->run();

        $this->console->writeAction('Waiting for full download to finish');
        $fullProcess->wait();

        $this->console->writeAction('Extracting files of full variant of Factorio');
        $this->createExtractArchiveProcess($fullArchive, $fullDirectory)->run();

        $this->console->writeAction('Patching headless variant with files from the full variant');
        $this->patchHeadless($headlessDirectory, $fullDirectory);

        $this->console->writeAction('Replacing previously installed version of Factorio');
        $this->replaceOldVersion($headlessDirectory);

        $this->console->writeAction('Cleaning up temporary files');
        $this->fileSystem->remove([
            $headlessDirectory,
            $fullDirectory,
            $headlessArchive,
            $fullArchive,
        ]);

        $this->console->writeMessage('Done.');
    }

    /**
     * Creates the process to download the game.
     * @param string $variant
     * @param string $version
     * @param string $destinationFile
     * @return DownloadProcess<string>
     */
    protected function createDownloadProcess(string $variant, string $version, string $destinationFile): DownloadProcess
    {
        $downloadUrl = sprintf(self::DOWNLOAD_URL, $version, $variant) . '?' . http_build_query([
            'username' => $this->factorioDownloadUsername,
            'token' => $this->factorioDownloadToken,
        ]);
        return new DownloadProcess($downloadUrl, $destinationFile);
    }

    /**
     * Extracts the downloaded archive to the specified directory.
     * @param string $archiveFile
     * @param string $directory
     * @return Process<string>
     */
    protected function createExtractArchiveProcess(string $archiveFile, string $directory): Process
    {
        $this->fileSystem->remove($directory);
        $this->fileSystem->mkdir($directory);

        $process = new Process(['tar', '-xf', $archiveFile, '-C', $directory]);
        $process->setTimeout(null);
        return $process;
    }

    /**
     * Patches the headless version of the game with files from the full one.
     * @param string $headlessDirectory
     * @param string $fullDirectory
     */
    protected function patchHeadless(string $headlessDirectory, string $fullDirectory): void
    {
        $this->fileSystem->remove([
            $headlessDirectory . '/factorio/data/base',
            $headlessDirectory . '/factorio/data/core',
        ]);
        $this->fileSystem->rename($fullDirectory . '/factorio/data/base', $headlessDirectory . '/factorio/data/base');
        $this->fileSystem->rename($fullDirectory . '/factorio/data/core', $headlessDirectory . '/factorio/data/core');
    }

    /**
     * Actually replaces the old version of Factorio currently installed.
     * @param string $headlessDirectory
     */
    protected function replaceOldVersion(string $headlessDirectory): void
    {
        $this->fileSystem->remove($this->factorioDirectory);
        $this->fileSystem->rename($headlessDirectory . '/factorio', $this->factorioDirectory);
    }
}
