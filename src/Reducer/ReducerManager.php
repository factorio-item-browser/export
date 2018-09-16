<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Reducer;

use FactorioItemBrowser\Export\Combination\ParentCombinationFinder;
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
     * The parent combination finder.
     * @var ParentCombinationFinder
     */
    protected $parentCombinationFinder;

    /**
     * The reducers to use.
     * @var array|ReducerInterface[]
     */
    protected $reducers;

    /**
     * Initializes the reducer manager.
     * @param MergerManager $mergerManager
     * @param ParentCombinationFinder $parentCombinationFinder
     * @param array|ReducerInterface[] $reducers
     */
    public function __construct(
        MergerManager $mergerManager,
        ParentCombinationFinder $parentCombinationFinder,
        array $reducers
    ) {
        $this->mergerManager = $mergerManager;
        $this->parentCombinationFinder = $parentCombinationFinder;
        $this->reducers = $reducers;
    }

    /**
     * Reduces the specified combination against its parents.
     * @param Combination $combination
     * @return Combination
     * @throws ExportException
     */
    public function reduce(Combination $combination): Combination
    {
        $mergedParentCombination = $this->createMergedParentCombination($combination);

        $result = clone($combination);
        $this->reduceCombination($result, $mergedParentCombination);
        return $result;
    }

    /**
     * Creates a merged combination of the parents of the specified combination.
     * @param Combination $combination
     * @return Combination
     * @throws ExportException
     */
    protected function createMergedParentCombination(Combination $combination): Combination
    {
        $result = new Combination();
        foreach ($this->parentCombinationFinder->find($combination) as $parentCombination) {
            $this->mergerManager->merge($result, $parentCombination);
        }
        return $result;
    }

    /**
     * Reduces the combination against its parent.
     * @param Combination $combination
     * @param Combination $parentCombination
     * @throws ReducerException
     */
    protected function reduceCombination(Combination $combination, Combination $parentCombination): void
    {
        foreach ($this->reducers as $reducer) {
            $reducer->reduce($combination, $parentCombination);
        }
    }
}
