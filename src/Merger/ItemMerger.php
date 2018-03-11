<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Merger;

use FactorioItemBrowser\ExportData\Entity\Item;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;

/**
 * The class merging items of combinations.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ItemMerger extends AbstractMerger
{
    /**
     * Merges the source combination into the destination one.
     * @param Combination $destination
     * @param Combination $source
     * @return $this
     */
    public function merge(Combination $destination, Combination $source)
    {
        foreach ($source->getItems() as $sourceItem) {
            $destinationItem = $destination->getItem($sourceItem->getType(), $sourceItem->getName());
            if ($destinationItem instanceof Item) {
                $this->mergeItem($destinationItem, $sourceItem);
            } else {
                $destination->addItem(clone($sourceItem));
            }
        }
        return $this;
    }

    /**
     * Merges the source item into the destination one.
     * @param Item $destination
     * @param Item $source
     * @return $this
     */
    protected function mergeItem(Item $destination, Item $source)
    {
        if (strlen($source->getIconHash()) > 0) {
            $destination->setIconHash($source->getIconHash());
        }

        $this->mergeLocalisedString($destination->getLabels(), $source->getLabels());
        $this->mergeLocalisedString($destination->getDescriptions(), $source->getDescriptions());
        return $this;
    }
}