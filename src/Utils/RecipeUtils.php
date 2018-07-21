<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Utils;

use FactorioItemBrowser\ExportData\Entity\Recipe;
use FactorioItemBrowser\ExportData\Entity\Recipe\Ingredient;
use FactorioItemBrowser\ExportData\Entity\Recipe\Product;

/**
 * The utils class of recipes.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class RecipeUtils
{
    /**
     * Calculates a hash of the specified recipe.
     * @param Recipe $recipe
     * @return string
     */
    public static function calculateHash(Recipe $recipe): string
    {
        $data = [
            'ct' => $recipe->getCraftingTime(),
            'cc' => $recipe->getCraftingCategory()
        ];

        $ingredients = $recipe->getIngredients();
        usort($ingredients, function (Ingredient $left, Ingredient $right): int {
            return $left->getOrder() <=> $right->getOrder();
        });
        foreach ($ingredients as $ingredient) {
            $data['i'][] = [
                $ingredient->getType(),
                $ingredient->getName(),
                $ingredient->getAmount()
            ];
        }

        $products = $recipe->getProducts();
        usort($products, function (Product $left, Product $right): int {
            return $left->getOrder() <=> $right->getOrder();
        });
        foreach ($products as $product) {
            $data['p'][] = [
                $product->getType(),
                $product->getName(),
                $product->getAmountMin(),
                $product->getAmountMax(),
                $product->getProbability()
            ];
        }

        return hash('crc32b', json_encode($data));
    }
}
