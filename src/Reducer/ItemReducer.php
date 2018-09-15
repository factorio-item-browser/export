<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Reducer;

use FactorioItemBrowser\Export\Exception\ReducerException;
use FactorioItemBrowser\ExportData\Entity\EntityInterface;
use FactorioItemBrowser\ExportData\Entity\Item;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;

/**
 * The class removing any items which did not change.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ItemReducer extends AbstractIdentifiedEntityReducer
{
    use LocalisedStringReducerTrait;

    /**
     * Returns the hashes to use from the specified combination.
     * @param Combination $combination
     * @return array|string[]
     */
    protected function getHashesFromCombination(Combination $combination): array
    {
        return $combination->getItemHashes();
    }

    /**
     * Reduces the item against its parent.
     * @param EntityInterface $entity
     * @param EntityInterface $parentEntity
     * @throws ReducerException
     */
    protected function reduceEntity(EntityInterface $entity, EntityInterface $parentEntity): void
    {
        if (!$entity instanceof Item || !$parentEntity instanceof Item) {
            throw new ReducerException('Internal type error.');
        }

        $this->reduceTranslationsOfItem($entity, $parentEntity);
        $this->reduceIconOfItem($entity, $parentEntity);
    }

    /**
     * Reduces the translations of the item.
     * @param Item $item
     * @param Item $parentItem
     */
    protected function reduceTranslationsOfItem(Item $item, Item $parentItem): void
    {
        $this->reduceLocalisedString($item->getLabels(), $parentItem->getLabels());
        $this->reduceLocalisedString($item->getDescriptions(), $parentItem->getDescriptions());

        if (count($item->getLabels()->getTranslations()) === 0
            && count($item->getDescriptions()->getTranslations()) === 0
            && $item->getProvidesRecipeLocalisation() === $parentItem->getProvidesRecipeLocalisation()
            && $item->getProvidesMachineLocalisation() === $parentItem->getProvidesMachineLocalisation()
        ) {
            $item->setProvidesRecipeLocalisation(false)
                 ->setProvidesMachineLocalisation(false);
        }
    }

    /**
     * Reduces the icon of the item.
     * @param Item $item
     * @param Item $parentItem
     */
    protected function reduceIconOfItem(Item $item, Item $parentItem): void
    {
        if ($item->getIconHash() === $parentItem->getIconHash()) {
            $item->setIconHash('');
        }
    }

    /**
     * Sets the hashes to the combination.
     * @param Combination $combination
     * @param array|string[] $hashes
     */
    protected function setHashesToCombination(Combination $combination, array $hashes): void
    {
        $combination->setItemHashes($hashes);
    }
}
