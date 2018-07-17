<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Reducer;

use FactorioItemBrowser\ExportData\Entity\Item;
use FactorioItemBrowser\ExportData\Entity\Mod\CombinationData;

/**
 * The class removing any items which did not change.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ItemReducer extends AbstractReducer
{
    /**
     * Reduces the specified combination data, removing any data which is identical in the parent combination.
     * @param CombinationData $combination
     * @param CombinationData $parentCombination
     * @return $this
     */
    public function reduce(CombinationData $combination, CombinationData $parentCombination)
    {
        foreach ($parentCombination->getItems() as $parentItem) {
            $item = $combination->getItem($parentItem->getType(), $parentItem->getName());
            if ($item instanceof Item) {
                $this->reduceLocalisedString($item->getLabels(), $parentItem->getLabels());
                $this->reduceLocalisedString($item->getDescriptions(), $parentItem->getDescriptions());
                if ($parentItem->getIconHash() === $item->getIconHash()) {
                    $item->setIconHash('');
                }

                if (count($item->getLabels()->getTranslations()) === 0
                    && count($item->getDescriptions()->getTranslations()) === 0
                    && strlen($item->getIconHash()) === 0
                    && $item->getProvidesRecipeLocalisation() === $parentItem->getProvidesRecipeLocalisation()
                    && $item->getProvidesMachineLocalisation() === $parentItem->getProvidesMachineLocalisation()
                ) {
                    $combination->removeItem($item->getType(), $item->getName());
                }
            }
        }
        $combination->setItems(array_values($combination->getItems()));
        return $this;
    }
}
