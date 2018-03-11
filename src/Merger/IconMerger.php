<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Merger;

use FactorioItemBrowser\ExportData\Entity\Icon;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;

/**
 * The class merging the icons of combinations.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class IconMerger extends AbstractMerger
{
    /**
     * Merges the source combination into the destination one.
     * @param Combination $destination
     * @param Combination $source
     * @return $this
     */
    public function merge(Combination $destination, Combination $source)
    {
        foreach ($source->getIcons() as $sourceIcon) {
            if (!$destination->getIcon($sourceIcon->getIconHash()) instanceof Icon) {
                $destination->addIcon(clone($sourceIcon));
            }
        }
        return $this;
    }
}