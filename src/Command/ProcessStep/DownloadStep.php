<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command\ProcessStep;

use FactorioItemBrowser\CombinationApi\Client\Constant\JobStatus;
use FactorioItemBrowser\Export\Entity\ProcessStepData;
use FactorioItemBrowser\Export\Service\ModDownloadService;

/**
 * The step for downloading all the required mods.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class DownloadStep implements ProcessStepInterface
{
    public function __construct(
        private readonly ModDownloadService $modDownloadService
    ) {
    }

    public function getLabel(): string
    {
        return 'Downloading mods from the Mod Portal';
    }

    public function getExportJobStatus(): string
    {
        return JobStatus::DOWNLOADING;
    }

    public function run(ProcessStepData $processStepData): void
    {
        $this->modDownloadService->download($processStepData->combination->modNames);
    }
}
