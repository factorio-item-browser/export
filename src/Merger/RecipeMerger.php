<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Merger;

use FactorioItemBrowser\Export\Exception\MergerException;
use FactorioItemBrowser\Export\Utils\LocalisedStringUtils;
use FactorioItemBrowser\ExportData\Entity\EntityInterface;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;
use FactorioItemBrowser\ExportData\Entity\Recipe;

/**
 * The class merging the recipes of combinations.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class RecipeMerger extends AbstractIdentifiedEntityMerger
{
    /**
     * Returns the hashes to use from the specified combination.
     * @param Combination $combination
     * @return array|string[]
     */
    protected function getHashesFromCombination(Combination $combination): array
    {
        return $combination->getRecipeHashes();
    }

    /**
     * Merges the source entity into the destination one.
     * @param EntityInterface $destination
     * @param EntityInterface $source
     * @throws MergerException
     */
    protected function mergeEntity(EntityInterface $destination, EntityInterface $source): void
    {
        if (!$destination instanceof Recipe || !$source instanceof Recipe) {
            throw new MergerException('Internal type error.');
        }

        $this->mergeData($destination, $source);
        $this->mergeTranslations($destination, $source);
        $this->mergeIcon($destination, $source);
    }

    /**
     * Merges the actual data of the source recipe to the destination one.
     * @param Recipe $destination
     * @param Recipe $source
     */
    protected function mergeData(Recipe $destination, Recipe $source): void
    {
        if (count($source->getIngredients()) > 0 || count($source->getProducts()) > 0) {
            $destination->setIngredients($source->getIngredients())
                        ->setProducts($source->getProducts())
                        ->setCraftingTime($source->getCraftingTime())
                        ->setCraftingCategory($source->getCraftingCategory());
        }
    }

    /**
     * Merges the translations from the destination recipe to the source one.
     * @param Recipe $destination
     * @param Recipe $source
     */
    protected function mergeTranslations(Recipe $destination, Recipe $source): void
    {
        LocalisedStringUtils::merge($destination->getLabels(), $source->getLabels());
        LocalisedStringUtils::merge($destination->getDescriptions(), $source->getDescriptions());
    }
    
    /**
     * Merges the icon from the destination recipe to the source one.
     * @param Recipe $destination
     * @param Recipe $source
     */
    protected function mergeIcon(Recipe $destination, Recipe $source): void
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
        $combination->setRecipeHashes($hashes);
    }
}
