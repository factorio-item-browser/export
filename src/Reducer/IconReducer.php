<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Reducer;

use FactorioItemBrowser\ExportData\Entity\Icon;
use FactorioItemBrowser\ExportData\Entity\Mod\CombinationData;

/**
 * The class removing any icons which already exist in the parent combination.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class IconReducer extends AbstractReducer
{
    /**
     * Reduces the specified combination data, removing any data which is identical in the parent combination.
     * @param CombinationData $combination
     * @param CombinationData $parentCombination
     * @return $this
     */
    public function reduce(CombinationData $combination, CombinationData $parentCombination)
    {
        foreach ($parentCombination->getIcons() as $parentIcon) {
            if ($combination->getIcon($parentIcon->getHash()) instanceof Icon) {
                $combination->removeIcon($parentIcon->getHash());
            }
        }
        $combination->setIcons(array_values($combination->getIcons()));
        return $this;
    }
}
