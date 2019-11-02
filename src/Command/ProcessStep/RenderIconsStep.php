<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command\ProcessStep;

use BluePsyduck\SymfonyProcessManager\ProcessManager;
use FactorioItemBrowser\Export\Console\Console;
use FactorioItemBrowser\Export\Entity\ProcessStepData;
use FactorioItemBrowser\Export\Process\RenderIconProcess;
use FactorioItemBrowser\ExportData\Entity\Icon;
use FactorioItemBrowser\ExportData\ExportData;
use FactorioItemBrowser\ExportQueue\Client\Constant\JobStatus;
use JMS\Serializer\SerializerInterface;

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
     * The serializer.
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * The number of parallel render processes.
     * @var int
     */
    protected $numberOfParallelRenderProcesses;

    /**
     * RenderIconsStep constructor.
     * @param Console $console
     * @param SerializerInterface $exportDataSerializer
     * @param int $numberOfParallelRenderProcesses
     */
    public function __construct(
        Console $console,
        SerializerInterface $exportDataSerializer,
        int $numberOfParallelRenderProcesses
    ) {
        $this->console = $console;
        $this->serializer = $exportDataSerializer;
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
            $processManager->addProcess($this->createProcessForIcon($icon));
        }
        $processManager->waitForAllProcesses();
    }

    /**
     * Creates the process manager to use for the download processes.
     * @param ExportData $exportData
     * @return ProcessManager
     */
    protected function createProcessManager(ExportData $exportData): ProcessManager
    {
        $result = new ProcessManager($this->numberOfParallelRenderProcesses);
        $result->setProcessStartCallback(function (RenderIconProcess $process): void {
            $this->console->writeAction('Rendering icon %s', $process->getIcon()->getId());
        });
        $result->setProcessFinishCallback(function (RenderIconProcess $process) use ($exportData): void {
            // @todo Check status.
            $exportData->addRenderedIcon($process->getIcon(), $process->getOutput());
        });
        return $result;
    }

    /**
     * Creates the render process for the icon.
     * @param Icon $icon
     * @return RenderIconProcess
     */
    protected function createProcessForIcon(Icon $icon): RenderIconProcess
    {
        return new RenderIconProcess($this->serializer, $icon);
    }
}
