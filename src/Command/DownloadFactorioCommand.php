<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command;

use FactorioItemBrowser\Export\AutoWire\Attribute\ReadDirectoryFromConfig;
use FactorioItemBrowser\Export\Constant\CommandName;
use FactorioItemBrowser\Export\Constant\ConfigKey;
use FactorioItemBrowser\Export\Output\Console;
use FactorioItemBrowser\Export\Service\FactorioDownloadService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * The process for downloading the Factorio game itself.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class DownloadFactorioCommand extends Command
{
    public function __construct(
        protected readonly Console $console,
        protected readonly FactorioDownloadService $factorioDownloadService,
        protected readonly Filesystem $fileSystem,
        #[ReadDirectoryFromConfig(ConfigKey::DIRECTORY_FACTORIO_FULL)]
        protected readonly string $fullFactorioDirectory,
        #[ReadDirectoryFromConfig(ConfigKey::DIRECTORY_FACTORIO_HEADLESS)]
        protected readonly string $headlessFactorioDirectory,
        #[ReadDirectoryFromConfig(ConfigKey::DIRECTORY_TEMP)]
        protected readonly string $tempDirectory,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        parent::configure();

        $this->setName(CommandName::DOWNLOAD_FACTORIO);
        $this->setDescription('Downloads the Factorio game itself.');

        $this->addArgument('version', InputArgument::REQUIRED, 'The version of Factorio to download.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $version = strval($input->getArgument('version'));
        $archiveFileFull = $this->tempDirectory . "/factorio_${version}_full.tar.xz";
        $archiveFileHeadless = $this->tempDirectory . "/factorio_${version}_headless.tar.xz";
        $tempDirectoryFull = $this->tempDirectory . "/factorio_${version}_full";
        $tempDirectoryHeadless = $this->tempDirectory . "/factorio_${version}_headless";

        $this->console->writeHeadline("Downloading and installing Factorio version {$version}");

        $this->console->writeAction('Downloading full variant of Factorio');
        $downloadFullProcess = $this->factorioDownloadService->createFactorioDownloadProcess(
            FactorioDownloadService::VARIANT_FULL,
            $version,
            $archiveFileFull,
        );
        $downloadFullProcess->start();

        $this->console->writeAction('Downloading headless variant of Factorio');
        $downloadHeadlessProcess = $this->factorioDownloadService->createFactorioDownloadProcess(
            FactorioDownloadService::VARIANT_HEADLESS,
            $version,
            $archiveFileHeadless,
        );
        $downloadHeadlessProcess->run();
        if ($downloadHeadlessProcess->getExitCode() !== 0) {
            $this->console->writeMessage('<fg=red>Download of headless version failed!</>');
            return 1;
        }
        $this->console->writeAction('Extracting headless variant of Factorio');
        $extractHeadlessProcess = $this->factorioDownloadService->createFactorioExtractProcess(
            $archiveFileHeadless,
            $tempDirectoryHeadless,
        );
        $extractHeadlessProcess->run();
        if ($extractHeadlessProcess->getExitCode() !== 0) {
            $this->console->writeMessage('<fg=red>Extracting headless version failed!</>');
            return 1;
        }

        $downloadFullProcess->wait();
        if ($downloadFullProcess->getExitCode() !== 0) {
            $this->console->writeMessage('<fg=red>Download of full version failed!</>');
            return 1;
        }

        $this->console->writeAction('Extracting full variant of Factorio');
        $extractFullProcess = $this->factorioDownloadService->createFactorioExtractProcess(
            $archiveFileFull,
            $tempDirectoryFull,
        );
        $extractFullProcess->run();
        if ($extractFullProcess->getExitCode() !== 0) {
            $this->console->writeMessage('<fg=red>Extracting full version failed!</>');
            return 1;
        }

        $this->console->writeAction('Switching Factorio releases');
        $this->fileSystem->remove($this->headlessFactorioDirectory);
        $this->fileSystem->rename($tempDirectoryHeadless, $this->headlessFactorioDirectory);
        $this->fileSystem->remove($this->fullFactorioDirectory);
        $this->fileSystem->rename($tempDirectoryFull, $this->fullFactorioDirectory);

        $this->console->writeAction('Cleaning up');
        $this->fileSystem->remove($archiveFileHeadless);
        $this->fileSystem->remove($archiveFileFull);

        $this->console->writeMessage('Done.');
        return 0;
    }
}
