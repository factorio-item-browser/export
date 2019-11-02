<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command\ProcessStep;

use FactorioItemBrowser\Export\Entity\ProcessStepData;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Factorio\Instance;
use FactorioItemBrowser\ExportQueue\Client\Constant\JobStatus;

/**
 * The step actually executing Factorio to get the dumped data.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class FactorioStep implements ProcessStepInterface
{
    /**
     * The Factorio instance.
     * @var Instance
     */
    protected $instance;

    /**
     * Initializes the step.
     * @param Instance $instance
     */
    public function __construct(Instance $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Returns the label to identify the step.
     * @return string
     */
    public function getLabel(): string
    {
        return 'Running Factorio';
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
     * @throws ExportException
     */
    public function run(ProcessStepData $processStepData): void
    {
        $combinationId = $processStepData->getExportJob()->getCombinationId();
        $modNames = $processStepData->getExportJob()->getModNames();

        $dump = $this->instance->run($combinationId, $modNames);

        $processStepData->setDump($dump);
    }
}
