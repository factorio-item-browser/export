<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Reducer;

use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Exception\ReducerException;
use FactorioItemBrowser\Export\Merger\MergerManager;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;

/**
 * The manager of the reducer classes.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ReducerManager
{
    /**
     * The merger manager.
     * @var MergerManager
     */
    protected $mergerManager;

    /**
     * The reducers to use.
     * @var array|ReducerInterface[]
     */
    protected $reducers;

    /**
     * Initializes the reducer manager.
     * @param MergerManager $mergerManager
     * @param array|AbstractReducer[] $reducers
     */
    public function __construct(?MergerManager $mergerManager, array $reducers)
    {
        $this->mergerManager = $mergerManager;
        $this->reducers = $reducers;
    }

    /**
     * Reduces the combination against its parent.
     * @param Combination $combination
     * @param Combination $parentCombination
     * @throws ReducerException
     */
    public function reduceCombination(Combination $combination, Combination $parentCombination): void
    {
        foreach ($this->reducers as $reducer) {
            $reducer->reduce($combination, $parentCombination);
        }
    }

//
//    /**
//     * Adds the combination to be reduced as soon as all parent combinations are ready.
//     * @param ExportCombination $combination
//     * @return $this
//     */
//    public function addCombination(ExportCombination $combination)
//    {
//        if (count($combination->getParentCombinations()) > 0) {
//            $this->pendingCombinations[] = $combination;
//            $this->checkForReducibleCombinations();
//        }
//        return $this;
//    }
//
//    /**
//     * Reduces all pending combinations.
//     * @return $this
//     */
//    public function reduceAllCombinations()
//    {
//        while (count($this->pendingCombinations) > 0) {
//            $count = count($this->pendingCombinations);
//            $this->checkForReducibleCombinations();
//            if (count($this->pendingCombinations) === $count) {
//                throw new ExportException('Unable to reduce any more combinations.');
//            }
//        }
//        return $this;
//    }
//
//    /**
//     * Checks for any combination which is currently reducible, and reduces them.
//     * @return $this
//     */
//    protected function checkForReducibleCombinations()
//    {
//        foreach ($this->pendingCombinations as $key => $pendingCombination) {
//            if ($this->isCombinationReducible($pendingCombination)) {
//                $mergedCombination = $this->mergerManager->mergeParentCombinations($pendingCombination);
//                $this->reduce($pendingCombination, $mergedCombination);
//                $pendingCombination->setIsReduced(true);
//                unset($this->pendingCombinations[$key]);
//            }
//        }
//        return $this;
//    }
//
//    /**
//     * Checks whether the specified combination is currently reducible.
//     * @param ExportCombination $combination
//     * @return bool
//     */
//    protected function isCombinationReducible(ExportCombination $combination): bool
//    {
//        $result = true;
//        foreach ($combination->getParentCombinations() as $parentCombination) {
//            if ($parentCombination instanceof ExportCombination && !$parentCombination->getIsReduced()) {
//                $result = false;
//                break;
//            }
//        }
//        return $result;
//    }
//
//    /**
//     * Reduces the specified combination with the parent one.
//     * @param Combination $combination
//     * @param Combination $parentCombination
//     * @return $this
//     */
//    protected function reduce(Combination $combination, Combination $parentCombination)
//    {
//        foreach ($this->reducers as $reducer) {
//            $reducer->reduce($combination->getData(), $parentCombination->getData());
//        }
//        return $this;
//    }
}
