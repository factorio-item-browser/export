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
    public function addCombination(Combination $combination)
    {
        return $this;
    }

    public function reduceAllCombinations()
    {
        return $this;
    }
}