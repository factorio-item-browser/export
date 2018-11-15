<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Reducer;

use FactorioItemBrowser\Export\Exception\ReducerException;
use FactorioItemBrowser\Export\Utils\LocalisedStringUtils;
use FactorioItemBrowser\ExportData\Entity\EntityInterface;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;
use FactorioItemBrowser\ExportData\Entity\Recipe;
use FactorioItemBrowser\ExportData\Entity\Recipe\Ingredient;
use FactorioItemBrowser\ExportData\Entity\Recipe\Product;
use FactorioItemBrowser\ExportData\Utils\EntityUtils;

/**
 * The class removing recipes which did not change.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class RecipeReducer extends AbstractIdentifiedEntityReducer
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
     * Reduces the entity against its parent.
     * @param EntityInterface $entity
     * @param EntityInterface $parentEntity
     * @throws ReducerException
     */
    protected function reduceEntity(EntityInterface $entity, EntityInterface $parentEntity): void
    {
        if (!$entity instanceof Recipe || !$parentEntity instanceof Recipe) {
            throw new ReducerException('Internal type error.');
        }

        $this->reduceData($entity, $parentEntity);
        $this->reduceTranslations($entity, $parentEntity);
        $this->reduceIcon($entity, $parentEntity);
    }

    /**
     * Reduces the data of the recipe.
     * @param Recipe $recipe
     * @param Recipe $parentRecipe
     */
    protected function reduceData(Recipe $recipe, Recipe $parentRecipe): void
    {
        if ($this->calculateDataHash($recipe) === $this->calculateDataHash($parentRecipe)) {
            $recipe->setIngredients([])
                   ->setProducts([])
                   ->setCraftingTime(0.)
                   ->setCraftingCategory('');
        }
    }

    /**
     * Calculates a data hash of the specified recipe.
     * @param Recipe $recipe
     * @return string
     */
    protected function calculateDataHash(Recipe $recipe): string
    {
        return EntityUtils::calculateHashOfArray([
            array_map(function (Ingredient $ingredient): string {
                return $ingredient->calculateHash();
            }, $recipe->getIngredients()),
            array_map(function (Product $product): string {
                return $product->calculateHash();
            }, $recipe->getProducts()),
            $recipe->getCraftingTime(),
            $recipe->getCraftingCategory(),
        ]);
    }

    /**
     * Reduces the translations of the recipe.
     * @param Recipe $recipe
     * @param Recipe $parentRecipe
     */
    protected function reduceTranslations(Recipe $recipe, Recipe $parentRecipe): void
    {
        LocalisedStringUtils::reduce($recipe->getLabels(), $parentRecipe->getLabels());
        LocalisedStringUtils::reduce($recipe->getDescriptions(), $parentRecipe->getDescriptions());
    }

    /**
     * Reduces the icon of the recipe.
     * @param Recipe $recipe
     * @param Recipe $parentRecipe
     */
    protected function reduceIcon(Recipe $recipe, Recipe $parentRecipe): void
    {
        if ($recipe->getIconHash() === $parentRecipe->getIconHash()) {
            $recipe->setIconHash('');
        }
    }

    /**
     * Returns whether the specified entity is actually empty.
     * @param EntityInterface $entity
     * @return bool
     * @throws ReducerException
     */
    protected function isEntityEmpty(EntityInterface $entity): bool
    {
        if (!$entity instanceof Recipe) {
            throw new ReducerException('Internal type error.');
        }

        return count($entity->getIngredients()) === 0
            && count($entity->getProducts()) === 0
            && LocalisedStringUtils::isEmpty($entity->getLabels())
            && LocalisedStringUtils::isEmpty($entity->getDescriptions())
            && $entity->getIconHash() === '';
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
