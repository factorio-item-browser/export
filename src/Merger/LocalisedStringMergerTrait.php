<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Merger;

use FactorioItemBrowser\ExportData\Entity\LocalisedString;

/**
 * The trait for merging localised strings.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
trait LocalisedStringMergerTrait
{
    /**
     * Merges the localised strings from the source to the destination.
     * @param LocalisedString $destination
     * @param LocalisedString $source
     */
    protected function mergeLocalisedStrings(LocalisedString $destination, LocalisedString $source): void
    {
        foreach ($source->getTranslations() as $locale => $translation) {
            $destination->setTranslation($locale, $translation);
        }
    }
}
