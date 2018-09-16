<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Reducer;

use FactorioItemBrowser\Export\Combination\ParentCombinationFinder;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Exception\ReducerException;
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
     * @param ParentCombinationFinder $parentCombinationFinder
     * @param array|ReducerInterface[] $reducers
     */
    public function __construct(ParentCombinationFinder $parentCombinationFinder, array $reducers)
    {
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
        $mergedParentCombination = $this->parentCombinationFinder->getMergedParentCombination($combination);

        $result = clone($combination);
        $this->reduceCombination($result, $mergedParentCombination);
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
