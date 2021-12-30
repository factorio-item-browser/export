<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Process;

use BluePsyduck\LaminasAutoWireFactory\Attribute\InjectAliasArray;
use FactorioItemBrowser\Export\Constant\ConfigKey;
use FactorioItemBrowser\Export\OutputProcessor\OutputProcessorInterface;
use FactorioItemBrowser\ExportData\ExportData;

/**
 * The factory for the FactorioProcess class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class FactorioProcessFactory
{
    /**
     * @param array<OutputProcessorInterface> $outputProcessors
     */
    public function __construct(
        #[InjectAliasArray(ConfigKey::MAIN, ConfigKey::OUTPUT_PROCESSORS)]
        private readonly array $outputProcessors,
    ) {
    }

    public function create(ExportData $exportData, string $instanceDirectory): FactorioProcess
    {
        return new FactorioProcess($this->outputProcessors, $exportData, $instanceDirectory);
    }
}
