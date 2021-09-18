<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command;

use FactorioItemBrowser\Export\Constant\CommandName;
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
    protected Console $console;
    protected FactorioDownloadService $factorioDownloadService;
    protected Filesystem $fileSystem;

    protected string $fullFactorioDirectory;
    protected string $headlessFactorioDirectory;
    protected string $tempDirectory;

    public function __construct(
        Console $console,
        FactorioDownloadService $factorioDownloadService,
        Filesystem $fileSystem,
        string $fullFactorioDirectory,
        string $headlessFactorioDirectory,
        string $tempDirectory,
    ) {
        parent::__construct();

        $this->console = $console;
        $this->factorioDownloadService = $factorioDownloadService;
        $this->fileSystem = $fileSystem;

        $this->fullFactorioDirectory = (string) realpath($fullFactorioDirectory);
        $this->headlessFactorioDirectory = (string) realpath($headlessFactorioDirectory);
        $this->tempDirectory = (string) realpath($tempDirectory);
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

        $this->console->writeHeadline("Downloading and installing Factorio version {$version}");

        $this->console->writeAction('Downloading full variant of Factorio');
        $fullProcess = $this->factorioDownloadService->createFactorioDownloadProcess(
            FactorioDownloadService::VARIANT_FULL,
            $version,
            $archiveFileFull,
        );
        $fullProcess->start();

        $this->console->writeAction('Downloading headless variant of Factorio');
        $headlessProcess = $this->factorioDownloadService->createFactorioDownloadProcess(
            FactorioDownloadService::VARIANT_HEADLESS,
            $version,
            $archiveFileHeadless,
        );
        $headlessProcess->run();

        $this->console->writeAction('Extracting headless variant of Factorio');
        $this->factorioDownloadService->extractFactorio($archiveFileHeadless, $this->headlessFactorioDirectory);

        $fullProcess->wait();
        $this->console->writeAction('Extracting full variant of Factorio');
        $this->factorioDownloadService->extractFactorio($archiveFileFull, $this->fullFactorioDirectory);

        $this->console->writeAction('Cleaning up');
        $this->fileSystem->remove($archiveFileHeadless);
        $this->fileSystem->remove($archiveFileFull);

        $this->console->writeMessage('Done.');
        return 0;
    }
}
