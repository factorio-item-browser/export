<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command\ProcessStep;

use BluePsyduck\SymfonyProcessManager\ProcessManager;
use BluePsyduck\SymfonyProcessManager\ProcessManagerInterface;
use FactorioItemBrowser\Export\Console\Console;
use FactorioItemBrowser\Export\Console\ProgressBar;
use FactorioItemBrowser\Export\Entity\ProcessStepData;
use FactorioItemBrowser\Export\Process\RenderIconProcess;
use FactorioItemBrowser\Export\Process\RenderIconProcessFactory;
use FactorioItemBrowser\ExportData\ExportData;
use FactorioItemBrowser\ExportQueue\Client\Constant\JobStatus;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The step for rendering all the icons and thumbnails.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class RenderIconsStep implements ProcessStepInterface
{
    protected Console $console;
    protected LoggerInterface $logger;
    protected RenderIconProcessFactory $renderIconProcessFactory;
    protected int $numberOfParallelRenderProcesses;

    protected OutputInterface $errorOutput;
    protected ProgressBar $progressBar;

    public function __construct(
        Console $console,
        LoggerInterface $logger,
        RenderIconProcessFactory $renderIconProcessFactory,
        int $numberOfParallelRenderProcesses
    ) {
        $this->console = $console;
        $this->logger = $logger;
        $this->renderIconProcessFactory = $renderIconProcessFactory;
        $this->numberOfParallelRenderProcesses = $numberOfParallelRenderProcesses;
    }

    public function getLabel(): string
    {
        return 'Rendering the thumbnails and icons';
    }

    public function getExportJobStatus(): string
    {
        return JobStatus::PROCESSING;
    }

    public function run(ProcessStepData $processStepData): void
    {
        $this->errorOutput = $this->console->createSection();
        $this->progressBar = $this->console->createProgressBar('Icons');
        $this->progressBar->setNumberOfSteps(count($processStepData->exportData->getIcons()));

        $processManager = $this->createProcessManager($processStepData->exportData);
        foreach ($processStepData->exportData->getIcons() as $icon) {
            $processManager->addProcess($this->renderIconProcessFactory->create($icon));
        }
        $processManager->waitForAllProcesses();
    }

    /**
     * Creates the process manager to use for the download processes.
     * @param ExportData $exportData
     * @return ProcessManagerInterface
     */
    protected function createProcessManager(ExportData $exportData): ProcessManagerInterface
    {
        $result = new ProcessManager($this->numberOfParallelRenderProcesses);
        $result->setProcessStartCallback(function (RenderIconProcess $process): void {
            $this->handleProcessStart($process);
        });
        $result->setProcessFinishCallback(function (RenderIconProcess $process) use ($exportData): void {
            $this->handleProcessFinish($exportData, $process);
        });
        return $result;
    }

    /**
     * Handles the start of a process.
     * @param RenderIconProcess<string> $process
     */
    protected function handleProcessStart(RenderIconProcess $process): void
    {
        $this->progressBar->start($process->getIcon()->id, '<fg=yellow>Rendering</> ' . $process->getIcon()->id);
    }

    /**
     * Handles the finishing of a process.
     * @param ExportData $exportData
     * @param RenderIconProcess<string> $process
     */
    protected function handleProcessFinish(ExportData $exportData, RenderIconProcess $process): void
    {
        $this->progressBar->finish($process->getIcon()->id);

        if ($process->isSuccessful()) {
            $exportData->getRenderedIcons()->set($process->getIcon()->id, $process->getOutput());
        } else {
            $errorOutput = trim($process->getErrorOutput());

            $this->logger->error($errorOutput, ['combination' => $exportData->getCombinationId()]);
            $this->errorOutput->write($errorOutput, false, ConsoleOutput::OUTPUT_RAW);
        }
    }
}
