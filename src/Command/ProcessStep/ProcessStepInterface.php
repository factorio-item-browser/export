<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command\ProcessStep;

use FactorioItemBrowser\Export\Entity\ProcessStepData;
use FactorioItemBrowser\Export\Exception\ExportException;

/**
 * The interface of the process steps.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
interface ProcessStepInterface
{
    /**
     * Returns the label to identify the step.
     * @return string
     */
    public function getLabel(): string;

    /**
     * Returns the status to set on the export job before running this step.
     * @return string
     */
    public function getExportJobStatus(): string;

    /**
     * Runs the process step.
     * @param ProcessStepData $processStepData
     * @throws ExportException
     */
    public function run(ProcessStepData $processStepData): void;
}
