<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Reducer;

use FactorioItemBrowser\Export\Exception\ReducerException;
use FactorioItemBrowser\ExportData\Entity\Item;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;
use FactorioItemBrowser\ExportData\Registry\EntityRegistry;

/**
 * The class removing any items which did not change.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ItemReducer implements ReducerInterface
{
    use LocalisedStringReducerTrait;

    /**
     * The registry of the raw items.
     * @var EntityRegistry
     */
    protected $rawItemRegistry;

    /**
     * The registry of the reduced items.
     * @var EntityRegistry
     */
    protected $reducedItemRegistry;

    /**
     * Initializes the reducer.
     * @param EntityRegistry $rawItemRegistry
     * @param EntityRegistry $reducedItemRegistry
     */
    public function __construct(EntityRegistry $rawItemRegistry, EntityRegistry $reducedItemRegistry)
    {
        $this->rawItemRegistry = $rawItemRegistry;
        $this->reducedItemRegistry = $reducedItemRegistry;
    }

    /**
     * Reduces the combination against the parent combination.
     * @param Combination $combination
     * @param Combination $parentCombination
     * @throws ReducerException
     */
    public function reduce(Combination $combination, Combination $parentCombination): void
    {
        $parentItemHashes = $this->mapItemHashes($parentCombination->getItemHashes());

        $reducedItemHashes = [];
        foreach ($combination->getItemHashes() as $itemHash) {
            $item = $this->fetchItemFromHash($itemHash);
            $identifier = $item->getIdentifier();

            $parentItemHash = $parentItemHashes[$identifier] ?? null;
            if ($parentItemHash === null) {
                $reducedItemHashes[] = $this->reducedItemRegistry->set($item);
            } elseif ($parentItemHash !== $itemHash) {
                $reducedItem = $this->createReducedItem($item, $this->fetchItemFromHash($parentItemHash));
                $reducedItemHashes[] = $this->reducedItemRegistry->set($reducedItem);
            }
        }
        $combination->setItemHashes($reducedItemHashes);
    }

    /**
     * Maps the item hashes to their instances.
     * @param array|string[] $itemHashes
     * @return array|string[]
     * @throws ReducerException
     */
    protected function mapItemHashes(array $itemHashes): array
    {
        $result = [];
        foreach ($itemHashes as $itemHash) {
            $item = $this->fetchItemFromHash($itemHash);
            $result[$item->getIdentifier()] = $itemHash;
        }
        return $result;
    }

    /**
     * Fetches the item with the specified hash.
     * @param string $itemHash
     * @return Item
     * @throws ReducerException
     */
    protected function fetchItemFromHash(string $itemHash): Item
    {
        $result = $this->rawItemRegistry->get($itemHash);
        if (!$result instanceof Item) {
            throw new ReducerException('Cannot find item with hash #' . $itemHash);
        }
        return $result;
    }

    /**
     * Reduces the item against its parent.
     * @param Item $item
     * @param Item $parentItem
     * @return Item
     */
    protected function createReducedItem(Item $item, Item $parentItem): Item
    {
        $result = clone($item);

        $this->reduceLocalisedString($result->getLabels(), $parentItem->getLabels());
        $this->reduceLocalisedString($result->getDescriptions(), $parentItem->getDescriptions());

        if (count($result->getLabels()->getTranslations()) === 0
            && count($result->getDescriptions()->getTranslations()) === 0
            && $result->getProvidesRecipeLocalisation() === $parentItem->getProvidesRecipeLocalisation()
            && $result->getProvidesMachineLocalisation() === $parentItem->getProvidesMachineLocalisation()
        ) {
            $result->setProvidesRecipeLocalisation(false)
                   ->setProvidesMachineLocalisation(false);
        }

        if ($result->getIconHash() === $parentItem->getIconHash()) {
            $result->setIconHash('');
        }

        return $result;
    }
}
