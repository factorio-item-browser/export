<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command\ProcessStep;

use FactorioItemBrowser\CombinationApi\Client\Constant\JobStatus;
use FactorioItemBrowser\Export\Entity\ProcessStepData;
use FactorioItemBrowser\Export\Output\Console;
use FactorioItemBrowser\Export\Service\FactorioExecutionService;

/**
 * The step actually executing Factorio to get the dumped data.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class FactorioStep implements ProcessStepInterface
{
    private Console $console;
    private FactorioExecutionService $factorioExecutionService;

    public function __construct(
        Console $console,
        FactorioExecutionService $factorioExecutionService
    ) {
        $this->console = $console;
        $this->factorioExecutionService = $factorioExecutionService;
    }

    public function getLabel(): string
    {
        return 'Running Factorio';
    }

    public function getExportJobStatus(): string
    {
        return JobStatus::PROCESSING;
    }

    public function run(ProcessStepData $processStepData): void
    {
        $combinationId = $processStepData->combination->id;
        $modNames = $processStepData->combination->modNames;

        try {
            $this->console->writeAction('Preparing factorio instance');
            $this->factorioExecutionService->prepare($combinationId, $modNames);

            $this->console->writeAction('Executing factorio');
            $processStepData->dump = $this->factorioExecutionService->execute($combinationId);
        } finally {
            $this->factorioExecutionService->cleanup($combinationId);
        }
    }
}
