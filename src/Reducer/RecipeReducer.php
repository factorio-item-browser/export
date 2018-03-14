<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Reducer;

use FactorioItemBrowser\Export\Utils\RecipeUtils;
use FactorioItemBrowser\ExportData\Entity\Mod\CombinationData;
use FactorioItemBrowser\ExportData\Entity\Recipe;

/**
 * The class removing recipes which did not change.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class RecipeReducer extends AbstractReducer
{
    /**
     * Reduces the specified combination data, removing any data which is identical in the parent combination.
     * @param CombinationData $combination
     * @param CombinationData $parentCombination
     * @return $this
     */
    public function reduce(CombinationData $combination, CombinationData $parentCombination)
    {
        foreach ($parentCombination->getRecipes() as $parentRecipe) {
            $recipe = $combination->getRecipe($parentRecipe->getName(), $parentRecipe->getMode());
            if ($recipe instanceof Recipe) {
                $this->reduceLocalisedString($recipe->getLabels(), $parentRecipe->getLabels());
                $this->reduceLocalisedString($recipe->getDescriptions(), $parentRecipe->getDescriptions());
                if ($recipe->getIconHash() === $parentRecipe->getIconHash()) {
                    $recipe->setIconHash('');
                }
                $recipeHash = RecipeUtils::calculateHash($recipe);
                $parentRecipeHash = RecipeUtils::calculateHash($parentRecipe);
                if ($recipeHash === $parentRecipeHash) {
                    if (count($recipe->getLabels()->getTranslations()) === 0
                        && count($recipe->getDescriptions()->getTranslations()) === 0
                        && strlen($recipe->getIconHash()) === 0
                    ) {
                        $combination->removeRecipe($recipe->getName(), $recipe->getMode());
                    } else {
                        $recipe->setIngredients([])
                               ->setProducts([])
                               ->setCraftingTime(0.);
                    }
                }
            }
        }

        $combination->setRecipes(array_values($combination->getRecipes()));
        return $this;
    }
}