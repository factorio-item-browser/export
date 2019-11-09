<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command\ProcessStep;

use FactorioItemBrowser\Export\Entity\ProcessStepData;
use FactorioItemBrowser\ExportQueue\Client\Constant\JobStatus;

/**
 * The final step when everything is done.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class DoneStep implements ProcessStepInterface
{
    /**
     * Returns the label to identify the step.
     * @return string
     */
    public function getLabel(): string
    {
        return 'Done.';
    }

    /**
     * Returns the status to set on the export job before running this step.
     * @return string
     */
    public function getExportJobStatus(): string
    {
        return JobStatus::UPLOADED;
    }

    /**
     * Runs the process step.
     * @param ProcessStepData $processStepData
     */
    public function run(ProcessStepData $processStepData): void
    {
    }
}
