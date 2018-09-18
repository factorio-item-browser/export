<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Utils;

use FactorioItemBrowser\ExportData\Entity\LocalisedString;

/**
 * The utils for the localised strings.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class LocalisedStringUtils
{
    /**
     * Checks whether the two specified localised strings are equal in all non-empty translations.
     * @param LocalisedString $left
     * @param LocalisedString $right
     * @return bool
     */
    public static function areEqual(LocalisedString $left, LocalisedString $right): bool
    {
        $result = true;
        foreach ($left->getTranslations() as $locale => $leftTranslation) {
            $rightTranslation = $right->getTranslation($locale);
            if ($leftTranslation !== '' && $rightTranslation !== '' && $leftTranslation !== $rightTranslation) {
                $result = false;
                break;
            }
        }
        return $result;
    }

    /**
     * Merges the localised strings from the source to the destination.
     * @param LocalisedString $destination
     * @param LocalisedString $source
     */
    public static function merge(LocalisedString $destination, LocalisedString $source): void
    {
        foreach ($source->getTranslations() as $locale => $translation) {
            $destination->setTranslation($locale, $translation);
        }
    }

    /**
     * Reduces the specified localised string.
     * @param LocalisedString $localisedString
     * @param LocalisedString $parentLocalisedString
     */
    public static function reduce(LocalisedString $localisedString, LocalisedString $parentLocalisedString): void
    {
        foreach ($localisedString->getTranslations() as $locale => $translation) {
            if ($translation === $parentLocalisedString->getTranslation($locale)) {
                $localisedString->setTranslation($locale, '');
            }
        }
    }
}
