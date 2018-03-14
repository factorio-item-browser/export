<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Merger;

use FactorioItemBrowser\ExportData\Entity\Icon;
use FactorioItemBrowser\ExportData\Entity\Mod\CombinationData;

/**
 * The class merging the icons of combinations.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class IconMerger extends AbstractMerger
{
    /**
     * Merges the source combination data into the destination one.
     * @param CombinationData $destination
     * @param CombinationData $source
     * @return $this
     */
    public function merge(CombinationData $destination, CombinationData $source)
    {
        foreach ($source->getIcons() as $sourceIcon) {
            if (!$destination->getIcon($sourceIcon->getIconHash()) instanceof Icon) {
                $destination->addIcon(clone($sourceIcon));
            }
        }
        return $this;
    }
}