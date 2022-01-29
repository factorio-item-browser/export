<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\DataProcessor;

use BluePsyduck\LaminasAutoWireFactory\Attribute\ReadConfig;
use BluePsyduck\SymfonyProcessManager\ProcessManager;
use BluePsyduck\SymfonyProcessManager\ProcessManagerInterface;
use FactorioItemBrowser\Export\Constant\ConfigKey;
use FactorioItemBrowser\Export\Output\Console;
use FactorioItemBrowser\Export\Output\ProgressBar;
use FactorioItemBrowser\Export\Process\RenderIconProcess;
use FactorioItemBrowser\Export\Process\RenderIconProcessFactory;
use FactorioItemBrowser\ExportData\Entity\Icon;
use FactorioItemBrowser\ExportData\ExportData;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The data processor rendering the icons.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class IconRenderer implements DataProcessorInterface
{
    protected OutputInterface $errorOutput;
    protected ProgressBar $progressBar;

    public function __construct(
        private readonly Console $console,
        private readonly LoggerInterface $logger,
        private readonly RenderIconProcessFactory $renderIconProcessFactory,
        #[ReadConfig(ConfigKey::MAIN, ConfigKey::PARALLEL_RENDERS)]
        private readonly int $numberOfParallelRenderProcesses,
    ) {
    }

    public function process(ExportData $exportData): void
    {
        $icons = [];
        foreach ($exportData->getIcons() as $icon) {
            /* @var Icon $icon */
            $icons[$icon->id] = $icon; // De-duplicate the icons.
        }

        $this->errorOutput = $this->console->createSection();
        $this->progressBar = $this->console->createProgressBar('Render icons');
        $this->progressBar->setNumberOfSteps(count($icons));

        $processManager = $this->createProcessManager($exportData);
        foreach ($icons as $icon) {
            $processManager->addProcess($this->renderIconProcessFactory->create($icon));
        }
        $processManager->waitForAllProcesses();
    }

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

    protected function handleProcessStart(RenderIconProcess $process): void
    {
        $this->progressBar->start($process->getIcon()->id, '<fg=yellow>Rendering</> ' . $process->getIcon()->id);
    }

    protected function handleProcessFinish(ExportData $exportData, RenderIconProcess $process): void
    {
        $this->progressBar->finish($process->getIcon()->id);

        if ($process->isSuccessful()) {
            $exportData->getRenderedIcons()->set($process->getIcon()->id, $process->getOutput());
        } else {
            $errorOutput = trim($process->getErrorOutput());

            $this->logger->error($errorOutput, ['combination' => $exportData->getCombinationId()]);
            $this->errorOutput->write($errorOutput, false, OutputInterface::OUTPUT_RAW);
        }
    }
}
