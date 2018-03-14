<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Merger;

use FactorioItemBrowser\ExportData\Entity\Mod\CombinationData;
use FactorioItemBrowser\ExportData\Entity\Recipe;

/**
 * The class merging the recipes of combinations.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class RecipeMerger extends AbstractMerger
{
    /**
     * Merges the source combination data into the destination one.
     * @param CombinationData $destination
     * @param CombinationData $source
     * @return $this
     */
    public function merge(CombinationData $destination, CombinationData $source)
    {
        foreach ($source->getRecipes() as $sourceRecipe) {
            $destinationRecipe = $destination->getRecipe($sourceRecipe->getName(), $sourceRecipe->getMode());
            if ($destinationRecipe instanceof Recipe) {
                $this->mergeRecipe($destinationRecipe, $sourceRecipe);
            } else {
                $destination->addRecipe(clone($sourceRecipe));
            }
        }
        return $this;
    }

    /**
     * Merges the source recipe into the destination one.
     * @param Recipe $destination
     * @param Recipe $source
     * @return $this
     */
    protected function mergeRecipe(Recipe $destination, Recipe $source)
    {
        if (count($source->getIngredients()) > 0 || count($source->getProducts()) > 0) {
            $clonedSource = clone($source);
            $destination
                ->setIngredients($clonedSource->getIngredients())
                ->setProducts($clonedSource->getProducts())
                ->setCraftingTime($clonedSource->getCraftingTime());
        }

        if (strlen($source->getIconHash()) > 0) {
            $destination->setIconHash($source->getIconHash());
        }
        $this->mergeLocalisedString($destination->getLabels(), $source->getLabels());
        $this->mergeLocalisedString($destination->getDescriptions(), $source->getLabels());

        return $this;
    }
}