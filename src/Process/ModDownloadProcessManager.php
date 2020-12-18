<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Process;

use BluePsyduck\FactorioModPortalClient\Entity\Mod;
use BluePsyduck\FactorioModPortalClient\Entity\Release;
use BluePsyduck\SymfonyProcessManager\ProcessManager;
use BluePsyduck\SymfonyProcessManager\ProcessManagerInterface;
use FactorioItemBrowser\Export\Exception\DownloadFailedException;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Output\Console;
use FactorioItemBrowser\Export\Output\ProgressBar;
use FactorioItemBrowser\Export\Service\ModFileService;
use Psr\Log\LoggerInterface;

/**
 * The manager for the mod download processes.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ModDownloadProcessManager
{
    private Console $console;
    private LoggerInterface $logger;
    private ModDownloadProcessFactory $modDownloadProcessFactory;
    private ModFileService $modFileService;
    private int $numberOfParallelDownloads;

    private ProcessManagerInterface $processManager;
    private ProgressBar $progressBar;

    public function __construct(
        Console $console,
        LoggerInterface $logger,
        ModDownloadProcessFactory $modDownloadProcessFactory,
        ModFileService $modFileService,
        int $numberOfParallelDownloads
    ) {
        $this->console = $console;
        $this->logger = $logger;
        $this->modDownloadProcessFactory = $modDownloadProcessFactory;
        $this->modFileService = $modFileService;
        $this->numberOfParallelDownloads = $numberOfParallelDownloads;
    }

    private function initialize(): void
    {
        if (!isset($this->processManager)) {
            $this->processManager = new ProcessManager($this->numberOfParallelDownloads);
            $this->processManager->setProcessStartCallback(function (ModDownloadProcess $process): void {
                $this->handleProcessStart($process);
            });
            $this->processManager->setProcessFinishCallback(function (ModDownloadProcess $process): void {
                $this->handleProcessFinish($process);
            });
        }

        if (!isset($this->progressBar)) {
            $this->progressBar = $this->console->createProgressBar('Downloading mods');
        }
    }

    public function add(Mod $mod, Release $release): void
    {
        $this->initialize();
        $this->progressBar->setNumberOfSteps($this->progressBar->getNumberOfSteps() + 1);
        $this->processManager->addProcess($this->modDownloadProcessFactory->create($mod, $release));
    }

    public function wait(): void
    {
        $this->initialize();
        $this->processManager->waitForAllProcesses();
    }

    /**
     * @param ModDownloadProcess<string> $process
     */
    protected function handleProcessStart(ModDownloadProcess $process): void
    {
        $modName = $process->getMod()->getName();
        $this->logger->info("Downloading mod {$modName}", [
            'mod' => $modName,
            'version' => (string) $process->getRelease()->getVersion(),
        ]);
        $this->progressBar->start($modName, "<fg=yellow>Downloading</> {$modName}");
    }

    /**
     * @param ModDownloadProcess<string> $process
     * @throws ExportException
     */
    protected function handleProcessFinish(ModDownloadProcess $process): void
    {
        if (!$process->isSuccessful()) {
            throw new DownloadFailedException($process->getMod(), $process->getRelease(), 'Command failed.');
        }

        if (sha1_file($process->getDestinationFile()) !== $process->getRelease()->getSha1()) {
            unlink($process->getDestinationFile());
            throw new DownloadFailedException($process->getMod(), $process->getRelease(), 'Hash mismatch.');
        }

        $modName = $process->getMod()->getName();
        $this->logger->info("Extracting mod {$modName}", [
            'mod' => $modName,
            'version' =>  (string) $process->getRelease()->getVersion(),
        ]);
        $this->progressBar->update($modName, "<fg=blue>Extracting</> {$modName}");
        $this->modFileService->addModArchive($modName, $process->getDestinationFile());
        unlink($process->getDestinationFile());
        $this->progressBar->finish($modName);
    }
}
