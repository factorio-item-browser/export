<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Reducer;

use FactorioItemBrowser\ExportData\Entity\LocalisedString;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;

/**
 * The abstract class of the reducer classes.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
abstract class AbstractReducer
{
    /**
     * Reduces the specified combination, removing any data which is identical in the parent combination.
     * @param Combination $combination
     * @param Combination $parentCombination
     * @return $this
     */
    abstract public function reduce(Combination $combination, Combination $parentCombination);

    /**
     * Reduces the specified localised string.
     * @param LocalisedString $localisedString
     * @param LocalisedString $parentLocalisedString
     * @return $this
     */
    protected function reduceLocalisedString(LocalisedString $localisedString, LocalisedString $parentLocalisedString)
    {
        foreach ($localisedString->getTranslations() as $locale => $translation) {
            if ($translation === $parentLocalisedString->getTranslation($locale)) {
                $localisedString->setTranslation($locale, '');
            }
        }
        return $this;
    }
}