<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Reducer\Mod;

use FactorioItemBrowser\ExportData\Entity\Mod;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;
use FactorioItemBrowser\ExportData\Registry\EntityRegistry;

/**
 * The reducer of the combination hashes of mods.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class CombinationReducer implements ModReducerInterface
{
    /**
     * The reduced combination registry.
     * @var EntityRegistry
     */
    protected $reducedCombinationRegistry;

    /**
     * Initializes the reducer.
     * @param EntityRegistry $reducedCombinationRegistry
     */
    public function __construct(EntityRegistry $reducedCombinationRegistry)
    {
        $this->reducedCombinationRegistry = $reducedCombinationRegistry;
    }

    /**
     * Reduces the mod.
     * @param Mod $mod
     */
    public function reduce(Mod $mod): void
    {
        $mod->setCombinationHashes($this->filterCombinationHashes($mod->getCombinationHashes()));
    }

    /**
     * Filters the combination hashes to those actually existing in a reduced version.
     * @param array|string[] $combinationHashes
     * @return array|string[]
     */
    protected function filterCombinationHashes(array $combinationHashes): array
    {
        $result = [];
        foreach ($combinationHashes as $combinationHash) {
            $combination = $this->reducedCombinationRegistry->get($combinationHash);
            if ($combination instanceof Combination) {
                $result[] = $combinationHash;
            }
        }
        return $result;
    }
}
