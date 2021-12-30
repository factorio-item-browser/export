<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Entity;

use FactorioItemBrowser\CombinationApi\Client\Transfer\Combination;
use FactorioItemBrowser\CombinationApi\Client\Transfer\Job;
use FactorioItemBrowser\ExportData\ExportData;

/**
 * The data used for the steps of processing.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ProcessStepData
{
    public Job $exportJob;
    public Combination $combination;
    public ExportData $exportData;
}
