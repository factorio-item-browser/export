<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Merger;

use FactorioItemBrowser\ExportData\Entity\Mod\Combination;

/**
 * The class merging the icons of combinations.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class IconMerger implements MergerInterface
{
    /**
     * Merges the source combination into the destination one.
     * @param Combination $destination
     * @param Combination $source
     */
    public function merge(Combination $destination, Combination $source): void
    {
        $destination->setIconHashes(array_values(array_unique(array_merge(
            $destination->getIconHashes(),
            $source->getIconHashes()
        ))));
    }
}
