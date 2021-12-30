<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\DataProcessor;

use FactorioItemBrowser\ExportData\ExportData;

/**
 * The interface of the data processors.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
interface DataProcessorInterface
{
    /**
     * Processes the data of the export.
     */
    public function process(ExportData $exportData): void;
}
