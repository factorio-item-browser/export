<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command\ProcessStep;

use BluePsyduck\LaminasAutoWireFactory\Attribute\InjectAliasArray;
use FactorioItemBrowser\CombinationApi\Client\Constant\JobStatus;
use FactorioItemBrowser\Export\Constant\ConfigKey;
use FactorioItemBrowser\Export\DataProcessor\DataProcessorInterface;
use FactorioItemBrowser\Export\Entity\ProcessStepData;

/**
 * The step for processing the exported data.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class DataProcessorStep implements ProcessStepInterface
{
    /**
     * @param array<DataProcessorInterface> $dataProcessors
     */
    public function __construct(
        #[InjectAliasArray(ConfigKey::MAIN, ConfigKey::DATA_PROCESSORS)]
        private readonly array $dataProcessors,
    ) {
    }

    public function getLabel(): string
    {
        return 'Processing the exported data';
    }

    public function getExportJobStatus(): string
    {
        return JobStatus::PROCESSING;
    }

    public function run(ProcessStepData $processStepData): void
    {
        foreach ($this->dataProcessors as $processor) {
            $processor->process($processStepData->exportData);
        }
    }
}
