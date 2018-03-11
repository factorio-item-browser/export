<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Merger;

use FactorioItemBrowser\ExportData\Entity\LocalisedString;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;

/**
 * The abstract class of the mergers.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
abstract class AbstractMerger
{
    /**
     * Merges the source combination into the destination one.
     * @param Combination $destination
     * @param Combination $source
     * @return $this
     */
    abstract public function merge(Combination $destination, Combination $source);

    /**
     * Merges the source localised string into the destination one.
     * @param LocalisedString $destination
     * @param LocalisedString $source
     * @return $this
     */
    protected function mergeLocalisedString(LocalisedString $destination, LocalisedString $source)
    {
        foreach ($source->getTranslations() as $locale => $translation) {
            $destination->setTranslation($locale, $translation);
        }
        return $this;
    }
}