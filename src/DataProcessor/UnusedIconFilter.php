<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\DataProcessor;

use FactorioItemBrowser\Export\Output\Console;
use FactorioItemBrowser\ExportData\Entity\Icon;
use FactorioItemBrowser\ExportData\ExportData;

/**
 * The data processor filtering icons which are not used by any entity.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class UnusedIconFilter implements DataProcessorInterface
{
    public function __construct(
        private readonly Console $console,
    ) {
    }

    public function process(ExportData $exportData): void
    {
        foreach ($this->console->iterateWithProgressbar('Filter unused icons', $exportData->getIcons()) as $icon) {
            /* @var Icon $icon */
            if ($icon->id === '') {
                $exportData->getIcons()->remove($icon);
            }
        }
    }
}
