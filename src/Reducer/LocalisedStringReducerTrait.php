<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Reducer;

use FactorioItemBrowser\ExportData\Entity\LocalisedString;

/**
 * The trait for reducing localised strings.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
trait LocalisedStringReducerTrait
{
    /**
     * Reduces the specified localised string.
     * @param LocalisedString $localisedString
     * @param LocalisedString $parentLocalisedString
     */
    protected function reduceLocalisedString(
        LocalisedString $localisedString,
        LocalisedString $parentLocalisedString
    ): void {
        foreach ($localisedString->getTranslations() as $locale => $translation) {
            if ($translation === $parentLocalisedString->getTranslation($locale)) {
                $localisedString->setTranslation($locale, '');
            }
        }
    }
}
