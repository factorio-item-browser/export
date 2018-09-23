<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Merger;

use FactorioItemBrowser\Export\Exception\MergerException;
use FactorioItemBrowser\Export\Utils\LocalisedStringUtils;
use FactorioItemBrowser\ExportData\Entity\EntityInterface;
use FactorioItemBrowser\ExportData\Entity\Item;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;

/**
 * The class merging items of combinations.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ItemMerger extends AbstractIdentifiedEntityMerger
{
    /**
     * Returns the hashes to use from the specified combination.
     * @param Combination $combination
     * @return array|string[]
     */
    protected function getHashesFromCombination(Combination $combination): array
    {
        return $combination->getItemHashes();
    }

    /**
     * Merges the source entity into the destination one.
     * @param EntityInterface $destination
     * @param EntityInterface $source
     * @throws MergerException
     */
    protected function mergeEntity(EntityInterface $destination, EntityInterface $source): void
    {
        if (!$destination instanceof Item || !$source instanceof Item) {
            throw new MergerException('Internal type error.');
        }

        $this->mergeTranslations($destination, $source);
        $this->mergeIcon($destination, $source);
    }

    /**
     * Merges the translations from the destination item to the source one.
     * @param Item $destination
     * @param Item $source
     */
    protected function mergeTranslations(Item $destination, Item $source): void
    {
        if (count($source->getLabels()->getTranslations()) > 0
            || count($source->getDescriptions()->getTranslations()) > 0
        ) {
            LocalisedStringUtils::merge($destination->getLabels(), $source->getLabels());
            LocalisedStringUtils::merge($destination->getDescriptions(), $source->getDescriptions());
            $destination->setProvidesRecipeLocalisation($source->getProvidesRecipeLocalisation())
                        ->setProvidesMachineLocalisation($source->getProvidesMachineLocalisation());
        }
    }

    /**
     * Merges the icon from the destination item to the source one.
     * @param Item $destination
     * @param Item $source
     */
    protected function mergeIcon(Item $destination, Item $source): void
    {
        if ($source->getIconHash() !== '') {
            $destination->setIconHash($source->getIconHash());
        }
    }

    /**
     * Sets the hashes to the combination.
     * @param Combination $combination
     * @param array|string[] $hashes
     */
    protected function setHashesToCombination(Combination $combination, array $hashes): void
    {
        $combination->setItemHashes($hashes);
    }
}
