<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command\ProcessStep;

use BluePsyduck\SymfonyProcessManager\ProcessManager;
use BluePsyduck\SymfonyProcessManager\ProcessManagerInterface;
use FactorioItemBrowser\Export\Console\Console;
use FactorioItemBrowser\Export\Entity\ProcessStepData;
use FactorioItemBrowser\Export\Process\RenderIconProcess;
use FactorioItemBrowser\Export\Process\RenderIconProcessFactory;
use FactorioItemBrowser\ExportData\ExportData;
use FactorioItemBrowser\ExportQueue\Client\Constant\JobStatus;

/**
 * The step for rendering all the icons and thumbnails.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class RenderIconsStep implements ProcessStepInterface
{
    /**
     * The console.
     * @var Console
     */
    protected $console;

    /**
     * The render icon process factory.
     * @var RenderIconProcessFactory
     */
    protected $renderIconProcessFactory;

    /**
     * The number of parallel render processes.
     * @var int
     */
    protected $numberOfParallelRenderProcesses;

    /**
     * RenderIconsStep constructor.
     * @param Console $console
     * @param RenderIconProcessFactory $renderIconProcessFactory
     * @param int $numberOfParallelRenderProcesses
     */
    public function __construct(
        Console $console,
        RenderIconProcessFactory $renderIconProcessFactory,
        int $numberOfParallelRenderProcesses
    ) {
        $this->console = $console;
        $this->renderIconProcessFactory = $renderIconProcessFactory;
        $this->numberOfParallelRenderProcesses = $numberOfParallelRenderProcesses;
    }

    /**
     * Returns the label to identify the step.
     * @return string
     */
    public function getLabel(): string
    {
        return 'Rendering the thumbnails and icons';
    }

    /**
     * Returns the status to set on the export job before running this step.
     * @return string
     */
    public function getExportJobStatus(): string
    {
        return JobStatus::PROCESSING;
    }

    /**
     * Runs the process step.
     * @param ProcessStepData $processStepData
     */
    public function run(ProcessStepData $processStepData): void
    {
        $processManager = $this->createProcessManager($processStepData->getExportData());
        foreach ($processStepData->getExportData()->getCombination()->getIcons() as $icon) {
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
        $this->console->writeAction(sprintf('Rendering icon %s', $process->getIcon()->getId()));
    }

    /**
     * Handles the finishing of a process.
     * @param ExportData $exportData
     * @param RenderIconProcess<string> $process
     */
    protected function handleProcessFinish(ExportData $exportData, RenderIconProcess $process): void
    {
        if ($process->isSuccessful()) {
            $exportData->addRenderedIcon($process->getIcon(), $process->getOutput());
        } else {
            $this->console->writeData($process->getOutput());
        }
    }
}
