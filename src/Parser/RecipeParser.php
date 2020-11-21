<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Parser;

use FactorioItemBrowser\Common\Constant\EntityType;
use FactorioItemBrowser\Common\Constant\RecipeMode;
use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\Entity\Dump\Ingredient as DumpIngredient;
use FactorioItemBrowser\Export\Entity\Dump\Product as DumpProduct;
use FactorioItemBrowser\Export\Entity\Dump\Recipe as DumpRecipe;
use FactorioItemBrowser\Export\Helper\HashCalculator;
use FactorioItemBrowser\ExportData\Entity\Combination;
use FactorioItemBrowser\ExportData\Entity\Recipe as ExportRecipe;
use FactorioItemBrowser\ExportData\Entity\Recipe\Ingredient as ExportIngredient;
use FactorioItemBrowser\ExportData\Entity\Recipe\Product as ExportProduct;

/**
 * The class parsing the recipes of the dump.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class RecipeParser implements ParserInterface
{
    protected HashCalculator $hashCalculator;
    protected IconParser $iconParser;
    protected TranslationParser $translationParser;

    public function __construct(
        HashCalculator $hashCalculator,
        IconParser $iconParser,
        TranslationParser $translationParser
    ) {
        $this->hashCalculator = $hashCalculator;
        $this->iconParser = $iconParser;
        $this->translationParser = $translationParser;
    }

    public function prepare(Dump $dump): void
    {
    }

    public function parse(Dump $dump, Combination $combination): void
    {
        $recipes = [];
        foreach ($dump->normalRecipes as $dumpRecipe) {
            $normalRecipe = $this->mapRecipe($dumpRecipe, RecipeMode::NORMAL);
            $recipes[$this->hashCalculator->hashRecipe($normalRecipe)] = $normalRecipe;
        }

        foreach ($dump->expensiveRecipes as $dumpRecipe) {
            $expensiveRecipe = $this->mapRecipe($dumpRecipe, RecipeMode::EXPENSIVE);
            $hash = $this->hashCalculator->hashRecipe($expensiveRecipe);

            if (!isset($recipes[$hash])) {
                $recipes[$hash] = $expensiveRecipe;
            }
        }

        $combination->setRecipes(array_values($recipes));
    }

    protected function mapRecipe(DumpRecipe $dumpRecipe, string $mode): ExportRecipe
    {
        $exportRecipe = new ExportRecipe();
        $exportRecipe->setName($dumpRecipe->name)
                     ->setMode($mode)
                     ->setCraftingTime($dumpRecipe->craftingTime)
                     ->setCraftingCategory($dumpRecipe->craftingCategory);

        foreach ($dumpRecipe->ingredients as $dumpIngredient) {
            $exportIngredient = $this->mapIngredient($dumpIngredient);
            if ($this->isIngredientValid($exportIngredient)) {
                $exportRecipe->addIngredient($exportIngredient);
            }
        }
        foreach ($dumpRecipe->products as $dumpProduct) {
            $exportProduct = $this->mapProduct($dumpProduct);
            if ($this->isProductValid($exportProduct)) {
                $exportRecipe->addProduct($exportProduct);
            }
        }

        $this->translationParser->translate($exportRecipe->getLabels(), $dumpRecipe->localisedName);
        $this->translationParser->translate($exportRecipe->getDescriptions(), $dumpRecipe->localisedDescription);

        $exportRecipe->setIconId($this->mapIconId($exportRecipe));
        return $exportRecipe;
    }

    protected function mapIngredient(DumpIngredient $dumpIngredient): ExportIngredient
    {
        $exportIngredient = new ExportIngredient();
        $exportIngredient->setType($dumpIngredient->type)
                         ->setName($dumpIngredient->name)
                         ->setAmount($dumpIngredient->amount);
        return $exportIngredient;
    }

    protected function isIngredientValid(ExportIngredient $ingredient): bool
    {
        return $ingredient->getAmount() > 0;
    }

    protected function mapProduct(DumpProduct $dumpProduct): ExportProduct
    {
        $exportProduct = new ExportProduct();
        $exportProduct->setType($dumpProduct->type)
                      ->setName($dumpProduct->name)
                      ->setAmountMin($dumpProduct->amountMin)
                      ->setAmountMax($dumpProduct->amountMax)
                      ->setProbability($dumpProduct->probability);
        return $exportProduct;
    }

    protected function isProductValid(ExportProduct $product): bool
    {
        $amount = ($product->getAmountMin() + $product->getAmountMax()) / 2 * $product->getProbability();
        return $amount > 0;
    }

    protected function mapIconId(ExportRecipe $recipe): string
    {
        $iconId = $this->iconParser->getIconId(EntityType::RECIPE, $recipe->getName());

        // If the recipe does not have an own icon, it may fall back to its first product's icon.
        if ($iconId === '' && count($recipe->getProducts()) > 0) {
            $firstProduct = $recipe->getProducts()[0];
            $iconId = $this->iconParser->getIconId($firstProduct->getType(), $firstProduct->getName());
        }

        return $iconId;
    }

    public function validate(Combination $combination): void
    {
    }
}
