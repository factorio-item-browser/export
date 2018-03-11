<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Reducer;

use FactorioItemBrowser\ExportData\Entity\Mod\Combination;

/**
 *
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ReducerManager
{
    /**
     * The reducers to use.
     * @var array|AbstractReducer[]
     */
    protected $reducers;

    /**
     * Initializes the reducer manager.
     * @param array|AbstractReducer[] $reducers
     */
    public function __construct(array $reducers)
    {
        $this->reducers = $reducers;
    }

    // Temp
    public function reduce(Combination $combination, Combination $parentCombination)
    {
        foreach ($this->reducers as $reducer) {
            $reducer->reduce($combination, $parentCombination);
        }
        return $this;
    }

    public function addCombination(Combination $combination)
    {
        return $this;
    }

    public function reduceAllCombinations()
    {
        return $this;
    }
}