<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Combination;

use FactorioItemBrowser\ExportData\Entity\Mod;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;
use FactorioItemBrowser\ExportData\Registry\EntityRegistry;
use FactorioItemBrowser\ExportData\Registry\ModRegistry;

/**
 * The class able to find parent combinations of a certain one.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ParentCombinationFinder
{
    /**
     * The combination registry.
     * @var EntityRegistry
     */
    protected $combinationRegistry;

    /**
     * The mod registry.
     * @var ModRegistry
     */
    protected $modRegistry;

    /**
     * Initializes the parent combination finder.
     * @param ModRegistry $modRegistry
     * @param EntityRegistry $combinationRegistry
     */
    public function __construct(EntityRegistry $combinationRegistry, ModRegistry $modRegistry)
    {
        $this->combinationRegistry = $combinationRegistry;
        $this->modRegistry = $modRegistry;
    }

    /**
     * Finds and returns the parent combinations.
     * @param Combination $combination
     * @return array|Combination[]
     */
    public function find(Combination $combination): array
    {
        $parentCombinations = $this->findParentCombinations($combination);
        return $this->sortCombinations($parentCombinations);
    }

    /**
     * Finds the parent combinations.
     * @param Combination $combination
     * @return array|Combination[]
     */
    protected function findParentCombinations(Combination $combination): array
    {
        $result = [];
        foreach ($combination->getLoadedModNames() as $modName) {
            $mod = $this->modRegistry->get($modName);
            if ($mod instanceof Mod) {
                $result = array_merge(
                    $result,
                    $this->findParentCombinationsOfMod($combination, $mod)
                );
            }
        }
        return $result;
    }

    /**
     * Finds the parent combinations of the specified mod.
     * @param Combination $combination
     * @param Mod $mod
     * @return array|Combination[]
     */
    protected function findParentCombinationsOfMod(Combination $combination, Mod $mod): array
    {
        $result = [];
        foreach ($mod->getCombinationHashes() as $combinationHash) {
            $possibleCombination = $this->combinationRegistry->get($combinationHash);
            if ($possibleCombination instanceof Combination
                && $possibleCombination->getName() !== $combination->getName()
                && $this->isValidParentCombination($combination, $possibleCombination)
            ) {
                $result[$possibleCombination->getName()] = $possibleCombination;
            }
        }
        return $result;
    }

    /**
     * Returns whether the combination is a valid parent combination.
     * @param Combination $combination
     * @param Combination $parentCombination
     * @return bool
     */
    protected function isValidParentCombination(Combination $combination, Combination $parentCombination): bool
    {
        $result = true;
        foreach ($parentCombination->getLoadedModNames() as $modName) {
            if (!in_array($modName, $combination->getLoadedModNames(), true)) {
                $result = false;
                break;
            }
        }
        return $result;
    }

    /**
     * Sorts the specified combinations.
     * @param array|Combination[] $combinations
     * @return array|Combination[]
     */
    protected function sortCombinations(array $combinations): array
    {
        $modOrders = $this->getModOrders();
        $combinationOrders = $this->getCombinationOrders($combinations, $modOrders);

        uasort(
            $combinations,
            static function (Combination $left, Combination $right) use ($modOrders, $combinationOrders): int {
                $result = $modOrders[$left->getMainModName()] <=> $modOrders[$right->getMainModName()];
                if ($result === 0) {
                    $leftOrders = $combinationOrders[$left->getName()];
                    $rightOrders = $combinationOrders[$right->getName()];
                    $result = count($leftOrders) <=> count($rightOrders);
                    while ($result === 0 && count($leftOrders) > 0) {
                        $result = array_shift($leftOrders) <=> array_shift($rightOrders);
                    }
                }
                return $result;
            }
        );

        return $combinations;
    }

    /**
     * Returns the orders of the mods.
     * @return array|int[]
     */
    protected function getModOrders(): array
    {
        $result = [];
        foreach ($this->modRegistry->getAllNames() as $modName) {
            $mod = $this->modRegistry->get($modName);
            if ($mod instanceof Mod) {
                $result[$mod->getName()] = $mod->getOrder();
            }
        }
        return $result;
    }

    /**
     * Returns the orders of the combinations.
     * @param array|Combination[] $combinations
     * @param array|int[] $modOrders
     * @return array|int[][]
     */
    protected function getCombinationOrders(array $combinations, array $modOrders): array
    {
        $result = [];
        foreach ($combinations as $combination) {
            $orders = [];
            foreach ($combination->getLoadedModNames() as $modName) {
                $orders[] = $modOrders[$modName];
            }
            sort($orders);
            $result[$combination->getName()] = $orders;
        }
        return $result;
    }
}
