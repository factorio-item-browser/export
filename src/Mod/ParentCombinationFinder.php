<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Mod;

use FactorioItemBrowser\ExportData\Entity\Mod;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;
use FactorioItemBrowser\ExportData\Service\ExportDataService;

/**
 * The class able to find parent combinations of a certain one.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ParentCombinationFinder
{
    /**
     * The export data service.
     * @var ExportDataService
     */
    protected $exportDataService;

    /**
     * The orders of the mods.
     * @var array|int
     */
    protected $modOrders;

    /**
     * Initializes the parent combination finder.
     * @param ExportDataService $exportDataService
     */
    public function __construct(ExportDataService $exportDataService)
    {
        $this->exportDataService =  $exportDataService;
        $this->modOrders = $this->getModOrders();
    }

    /**
     * Returns the orders of the mods.
     * @return array|int[]
     */
    protected function getModOrders(): array
    {
        $result = [];
        foreach ($this->exportDataService->getMods() as $mod) {
            $result[$mod->getName()] = $mod->getOrder();
        }
        return $result;
    }

    /**
     * Finds any parent combination to the specified one.
     * @param Combination $combination
     * @param array|Combination[] $additionalCombinations Additional combinations to check which are not saved yet.
     * @return array|Combination[]
     */
    public function findParentCombinations(Combination $combination, array $additionalCombinations): array
    {
        $parentCombinations = [];
        foreach ($combination->getLoadedModNames() as $modName) {
            if ($modName !== $combination->getMainModName()) {
                $mod = $this->exportDataService->getMod($modName);
                if ($mod instanceof Mod) {
                    $parentCombinations = array_merge(
                        $parentCombinations,
                        $this->checkForParentCombinations($combination, $mod->getCombinations())
                    );
                }
            }
        }
        $parentCombinations = array_merge(
            $parentCombinations,
            $this->checkForParentCombinations($combination, $additionalCombinations)
        );

        $parentCombinations = $this->sortCombinations($parentCombinations);
        return $parentCombinations;
    }

    /**
     * @param Combination $combination
     * @param array|Combination[] $possibleParentCombinations
     * @return array
     */
    protected function checkForParentCombinations(Combination $combination, array $possibleParentCombinations): array
    {
        $result = [];
        foreach ($possibleParentCombinations as $possibleParentCombination) {
            if ($possibleParentCombination->getName() !== $combination->getName()
                && $this->isValidParentCombination($combination, $possibleParentCombination)
            ) {
                $result[$possibleParentCombination->getName()] = $possibleParentCombination;
            }
        }
        return $result;
    }

    /**
     * Returns whether the parent combination is valid.
     * @param Combination $combination
     * @param Combination $parentCombination
     * @return bool
     */
    protected function isValidParentCombination(Combination $combination, Combination $parentCombination): bool
    {
        $result = true;
        foreach ($parentCombination->getLoadedModNames() as $modName) {
            if (!in_array($modName, $combination->getLoadedModNames())) {
                $result = false;
                break;
            }
        }
        return $result;
    }

    /**
     * Sorts the combinations.
     * @param array|Combination[] $combinations
     * @return array|Combination[]
     */
    protected function sortCombinations(array $combinations): array
    {
        uasort($combinations, function (Combination $left, Combination $right): int {
            $result = $this->modOrders[$left->getMainModName()] <=> $this->modOrders[$right->getMainModName()];
            if ($result === 0) {
                $leftOrders = $this->mapModNamesToOrders($left->getLoadedModNames());
                $rightOrders = $this->mapModNamesToOrders($right->getLoadedModNames());
                $result = count($leftOrders) <=> count($rightOrders);
                while ($result === 0 && !empty($leftOrders)) {
                    $result = array_shift($leftOrders) <=> array_shift($rightOrders);
                }
            }
            return $result;
        });
        return $combinations;
    }

    /**
     * Maps the mod names to their orders.
     * @param array|string[] $modNames
     * @return array|int[]
     */
    protected function mapModNamesToOrders(array $modNames): array
    {
        $result = [];
        foreach ($modNames as $modName) {
            $result[] = $this->modOrders[$modName];
        }
        sort($result);
        return $result;
    }
}