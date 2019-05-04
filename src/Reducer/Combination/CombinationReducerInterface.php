<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Reducer\Combination;

use FactorioItemBrowser\Export\Exception\ReducerException;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;

/**
 * The interface of the combination reducers.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
interface CombinationReducerInterface
{
    /**
     * Reduces the combination against the parent combination.
     * @param Combination $combination
     * @param Combination $parentCombination
     * @throws ReducerException
     */
    public function reduce(Combination $combination, Combination $parentCombination): void;

    /**
     * Persists the data of the specified combination.
     * @param Combination $combination
     * @throws ReducerException
     */
    public function persist(Combination $combination): void;
}
