<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Reducer\Combination;

use FactorioItemBrowser\Export\Exception\ReducerException;
use FactorioItemBrowser\ExportData\Entity\Icon;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;
use FactorioItemBrowser\ExportData\Registry\EntityRegistry;

/**
 * The class removing any icons which already exist in the parent combination.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class IconReducer implements CombinationReducerInterface
{
    /**
     * The registry of the raw icons.
     * @var EntityRegistry
     */
    protected $rawIconRegistry;

    /**
     * The registry of the reduced icons.
     * @var EntityRegistry
     */
    protected $reducedIconRegistry;

    /**
     * Initializes the reducer.
     * @param EntityRegistry $rawIconRegistry
     * @param EntityRegistry $reducedIconRegistry
     */
    public function __construct(EntityRegistry $rawIconRegistry, EntityRegistry $reducedIconRegistry)
    {
        $this->rawIconRegistry = $rawIconRegistry;
        $this->reducedIconRegistry = $reducedIconRegistry;
    }

    /**
     * Reduces the combination against the parent combination.
     * @param Combination $combination
     * @param Combination $parentCombination
     */
    public function reduce(Combination $combination, Combination $parentCombination): void
    {
        $iconHashes = array_values(array_diff($combination->getIconHashes(), $parentCombination->getIconHashes()));
        $combination->setIconHashes($iconHashes);
    }

    /**
     * Persists the data of the specified combination.
     * @param Combination $combination
     * @throws ReducerException
     */
    public function persist(Combination $combination): void
    {
        foreach ($combination->getIconHashes() as $iconHash) {
            $icon = $this->rawIconRegistry->get($iconHash);
            if ($icon instanceof Icon) {
                $this->reducedIconRegistry->set($icon);
            } else {
                throw new ReducerException('Cannot find icon with hash #' . $iconHash);
            }
        }
    }
}
