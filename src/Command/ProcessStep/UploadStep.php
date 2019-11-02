<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command\ProcessStep;

use FactorioItemBrowser\Export\Entity\ProcessStepData;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\ExportQueue\Client\Constant\JobStatus;

/**
 * The step for uploading the export file to the importer.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class UploadStep implements ProcessStepInterface
{
    /**
     * Returns the label to identify the step.
     * @return string
     */
    public function getLabel(): string
    {
        return 'Uploading export file to importer';
    }

    /**
     * Returns the status to set on the export job before running this step.
     * @return string
     */
    public function getExportJobStatus(): string
    {
        return JobStatus::UPLOADING;
    }

    /**
     * Runs the process step.
     * @param ProcessStepData $processStepData
     * @throws ExportException
     */
    public function run(ProcessStepData $processStepData): void
    {
        $fileName = $processStepData->getExportData()->persist();

        echo 'Saved to: ' . $fileName . PHP_EOL;
    }
}
