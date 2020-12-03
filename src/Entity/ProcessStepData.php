<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Entity;

use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\ExportData\ExportData;
use FactorioItemBrowser\ExportQueue\Client\Entity\Job;

/**
 * The data used for the steps of processing.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ProcessStepData
{
    public Job $exportJob;
    public ExportData $exportData;
    public Dump $dump;
}
