<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Parser;

use BluePsyduck\Common\Data\DataContainer;
use FactorioItemBrowser\Export\Utils\RecipeUtils;
use FactorioItemBrowser\ExportData\Entity\Item;
use FactorioItemBrowser\ExportData\Entity\Mod\CombinationData;
use FactorioItemBrowser\ExportData\Entity\Recipe;
use FactorioItemBrowser\ExportData\Entity\Recipe\Ingredient;
use FactorioItemBrowser\ExportData\Entity\Recipe\Product;

/**
 * The class parsing the recipes of the dump.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class RecipeParser extends AbstractParser
{
    /**
     * Parses the dump data into the combination.
     * @param CombinationData $combinationData
     * @param DataContainer $dumpData
     * @return $this
     */
    public function parse(CombinationData $combinationData, DataContainer $dumpData)
    {
        $normalRecipes = [];
        foreach ($dumpData->getObjectArray(['recipes', 'normal']) as $recipeData) {
            $recipe = $this->parseRecipe($recipeData, 'normal');
            $combinationData->addRecipe($recipe);
            $normalRecipes[$recipe->getName()] = $recipe;
        }

        foreach ($dumpData->getObjectArray(['recipes', 'expensive']) as $recipeData) {
            $recipe = $this->parseRecipe($recipeData, 'expensive');
            if (!isset($normalRecipes[$recipe->getName()])
                || RecipeUtils::calculateHash($recipe) !== RecipeUtils::calculateHash($normalRecipes[$recipe->getName()])
            ) {
                $combinationData->addRecipe($recipe);
            }
        }
        $this->removeDuplicateTranslations($combinationData);
        return $this;
    }

    /**
     * Parses the recipe data into an entity.
     * @param DataContainer $recipeData
     * @param string $mode
     * @return Recipe
     */
    protected function parseRecipe(DataContainer $recipeData, string $mode): Recipe
    {
        $recipe = new Recipe();
        $recipe
            ->setName($recipeData->getString('name'))
            ->setMode($mode)
            ->setCraftingTime($recipeData->getFloat('craftingTime'))
            ->setCraftingCategory($recipeData->getString('craftingCategory'));

        $this->translator->addTranslations(
            $recipe->getLabels(),
            'name',
            $recipeData->get(['localised', 'name']),
            ''
        );
        $this->translator->addTranslations(
            $recipe->getDescriptions(),
            'description',
            $recipeData->get(['localised', 'description']),
            ''
        );

        $order = 1;
        foreach ($recipeData->getObjectArray('ingredients') as $ingredientData) {
            $ingredient = $this->parseIngredient($ingredientData, $order);
            if ($ingredient instanceof Ingredient) {
                $recipe->addIngredient($ingredient);
                ++$order;
            }
        }

        $order = 1;
        foreach ($recipeData->getObjectArray('products') as $productData) {
            $product = $this->parseProduct($productData, $order);
            if ($product instanceof Product) {
                $recipe->addProduct($product);
                ++$order;
            }
        }

        return $recipe;
    }

    /**
     * Parses the ingredient data into an entity.
     * @param DataContainer $ingredientData
     * @param int $order
     * @return Ingredient|null
     */
    protected function parseIngredient(DataContainer $ingredientData, int $order): ?Ingredient
    {
        $ingredient = new Ingredient();
        $ingredient
            ->setType($ingredientData->getString('type'))
            ->setName($ingredientData->getString('name'))
            ->setAmount($ingredientData->getFloat('amount'))
            ->setOrder($order);

        return ($ingredient->getAmount() > 0) ? $ingredient : null;
    }

    /**
     * Parses the product data into an entity.
     * @param DataContainer $productData
     * @param int $order
     * @return Product|null
     */
    protected function parseProduct(DataContainer $productData, int $order): ?Product
    {
        $product = new Product();
        $product
            ->setType($productData->getString('type'))
            ->setName($productData->getString('name'))
            ->setAmountMin($productData->getFloat('amountMin'))
            ->setAmountMax($productData->getFloat('amountMax'))
            ->setProbability($productData->getFloat('probability'))
            ->setOrder($order);

        $amount = ($product->getAmountMin() + $product->getAmountMax()) / 2 * $product->getProbability();
        return ($amount > 0) ? $product : null;
    }

    /**
     * Removes duplicate translations if the item are already providing them.
     * @param CombinationData $combinationData
     * @return $this
     */
    protected function removeDuplicateTranslations(CombinationData $combinationData)
    {
        foreach ($combinationData->getRecipes() as $recipe) {
            /* @var Item[] $items */
            $items = array_filter([
                $combinationData->getItem('item', $recipe->getName()),
                $combinationData->getItem('fluid', $recipe->getName())
            ]);
            foreach ($items as $item) {
                if ($this->areLocalisedStringsIdentical($item->getLabels(), $recipe->getLabels())
                    && $this->areLocalisedStringsIdentical($item->getDescriptions(), $recipe->getDescriptions())
                ) {
                    $recipe->getLabels()->readData(new DataContainer([]));
                    $recipe->getDescriptions()->readData(new DataContainer([]));
                    $item->setProvidesRecipeLocalisation(true);
                    break;
                }
            }
        }
        return $this;
    }
}