<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Service;

use FactorioItemBrowser\Export\Process\DownloadProcess;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

/**
 * The service helping with downloading the Factorio game from official sources.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class FactorioDownloadService
{
    public const VARIANT_FULL = 'full';
    public const VARIANT_HEADLESS = 'headless';

    private const FACTORIO_DOWNLOAD_URLS = [
        self::VARIANT_FULL => 'https://www.factorio.com/get-download/%s/alpha/linux64',
        self::VARIANT_HEADLESS => 'https://www.factorio.com/get-download/%s/headless/linux64',
    ];

    private Filesystem $fileSystem;
    private string $factorioApiUsername;
    private string $factorioApiToken;
    private string $tempDirectory;

    public function __construct(
        Filesystem $filesystem,
        string $factorioApiUsername,
        string $factorioApiToken,
        string $tempDirectory
    ) {
        $this->fileSystem = $filesystem;
        $this->factorioApiUsername = $factorioApiUsername;
        $this->factorioApiToken = $factorioApiToken;
        $this->tempDirectory = (string) realpath($tempDirectory);
    }

    /**
     * Creates a process to download an instance of Factorio.
     * @param string $variant
     * @param string $version
     * @param string $destinationFile
     * @return DownloadProcess<string>
     */
    public function createFactorioDownloadProcess(
        string $variant,
        string $version,
        string $destinationFile,
    ): DownloadProcess {
        return new DownloadProcess(
            $this->buildUrl(sprintf(self::FACTORIO_DOWNLOAD_URLS[$variant], $version)),
            $destinationFile,
        );
    }

    /**
     * Extracts the specified archive file and replaces the current Factorio instance with its contents.
     * @param string $archiveFile
     * @param string $destinationDirectory
     */
    public function extractFactorio(string $archiveFile, string $destinationDirectory): void
    {
        $tempDirectory = $this->tempDirectory . '/factorio_temp';

        $process = $this->createExtractArchiveProcess($archiveFile, $tempDirectory);
        $process->run();

        $this->fileSystem->remove($destinationDirectory);
        $this->fileSystem->rename($tempDirectory, $destinationDirectory);
    }

    /**
     * @param string $archiveFile
     * @param string $destinationDirectory
     * @return Process<string>
     */
    protected function createExtractArchiveProcess(string $archiveFile, string $destinationDirectory): Process
    {
        $this->fileSystem->remove($destinationDirectory);
        $this->fileSystem->mkdir($destinationDirectory);

        $process = new Process(['tar', '-xf', $archiveFile, '-C', $destinationDirectory, '--strip', '1']);
        $process->setTimeout(null);
        return $process;
    }

    /**
     * @param string $url
     * @param array<string, string> $parameters
     * @return string
     */
    protected function buildUrl(string $url, array $parameters = []): string
    {
        return $url . '?' . http_build_query(array_merge($parameters, [
            'username' => $this->factorioApiUsername,
            'token' => $this->factorioApiToken,
        ]));
    }
}
