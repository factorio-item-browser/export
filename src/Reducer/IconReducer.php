<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Reducer;

use FactorioItemBrowser\ExportData\Entity\Icon;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;

/**
 * The class removing any icons which already exist in the parent combination.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class IconReducer extends AbstractReducer
{
    /**
     * Reduces the specified combination, removing any data which is identical in the parent combination.
     * @param Combination $combination
     * @param Combination $parentCombination
     * @return $this
     */
    public function reduce(Combination $combination, Combination $parentCombination)
    {
        foreach ($parentCombination->getIcons() as $parentIcon) {
            if ($combination->getIcon($parentIcon->getIconHash()) instanceof Icon) {
                $combination->removeIcon($parentIcon->getIconHash());
            }
        }
        $combination->setIcons(array_values($combination->getIcons()));
        return $this;
    }
}