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
    public function getLabel(): string
    {
        return 'Done.';
    }

    public function getExportJobStatus(): string
    {
        return JobStatus::UPLOADED;
    }

    public function run(ProcessStepData $processStepData): void
    {
    }
}
