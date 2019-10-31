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
    /**
     * The hash calculator.
     * @var HashCalculator
     */
    protected $hashCalculator;

    /**
     * The icon parser.
     * @var IconParser
     */
    protected $iconParser;

    /**
     * The translation parser.
     * @var TranslationParser
     */
    protected $translationParser;

    /**
     * Initializes the parser.
     * @param HashCalculator $hashCalculator
     * @param IconParser $iconParser
     * @param TranslationParser $translationParser
     */
    public function __construct(
        HashCalculator $hashCalculator,
        IconParser $iconParser,
        TranslationParser $translationParser
    ) {
        $this->hashCalculator = $hashCalculator;
        $this->iconParser = $iconParser;
        $this->translationParser = $translationParser;
    }

    /**
     * Prepares the parser to be able to later parse the dump.
     * @param Dump $dump
     */
    public function prepare(Dump $dump): void
    {
    }

    /**
     * Parses the data from the dump into the combination.
     * @param Dump $dump
     * @param Combination $combination
     */
    public function parse(Dump $dump, Combination $combination): void
    {
        $normalRecipes = [];
        foreach ($dump->getControlStage()->getNormalRecipes() as $dumpRecipe) {
            $exportRecipe = $this->mapRecipe($dumpRecipe, RecipeMode::NORMAL);
            $normalRecipes[$exportRecipe->getName()] = $exportRecipe;
            $combination->addRecipe($exportRecipe);
        }

        foreach ($dump->getControlStage()->getExpensiveRecipes() as $dumpRecipe) {
            $expensiveRecipe = $this->mapRecipe($dumpRecipe, RecipeMode::EXPENSIVE);
            $normalRecipe = $normalRecipes[$expensiveRecipe->getName()] ?? null;

            if (
                $normalRecipe === null
                || $this->hashCalculator->hashRecipe($normalRecipe)
                    !== $this->hashCalculator->hashRecipe($expensiveRecipe)
            ) {
                $combination->addRecipe($expensiveRecipe);
            }
        }
    }

    /**
     * Maps a dump recipe to an export one.
     * @param DumpRecipe $dumpRecipe
     * @param string $mode
     * @return ExportRecipe
     */
    protected function mapRecipe(DumpRecipe $dumpRecipe, string $mode): ExportRecipe
    {
        $exportRecipe = new ExportRecipe();
        $exportRecipe->setName(strtolower($dumpRecipe->getName()))
                     ->setMode($mode)
                     ->setCraftingTime($dumpRecipe->getCraftingTime())
                     ->setCraftingCategory($dumpRecipe->getCraftingCategory());

        foreach ($dumpRecipe->getIngredients() as $dumpIngredient) {
            $exportIngredient = $this->mapIngredient($dumpIngredient);
            if ($exportIngredient->getAmount() > 0) {
                $exportRecipe->addIngredient($this->mapIngredient($dumpIngredient));
            }
        }
        foreach ($dumpRecipe->getProducts() as $dumpProduct) {
            $exportProduct = $this->mapProduct($dumpProduct);
            $productAmount = ($exportProduct->getAmountMin() + $exportProduct->getAmountMax())
                / 2 * $exportProduct->getProbability();
            if ($productAmount > 0) {
                $exportRecipe->addProduct($this->mapProduct($dumpProduct));
            }
        }

        $this->translationParser->translateNames($exportRecipe->getLabels(), $dumpRecipe->getLocalisedName());
        $this->translationParser->translateDescriptions(
            $exportRecipe->getDescriptions(),
            $dumpRecipe->getLocalisedDescription()
        );

        $exportRecipe->setIconId($this->mapIconHash($exportRecipe));
        return $exportRecipe;
    }

    /**
     * Maps the dump ingredient to an export one.
     * @param DumpIngredient $dumpIngredient
     * @return ExportIngredient
     */
    protected function mapIngredient(DumpIngredient $dumpIngredient): ExportIngredient
    {
        $exportIngredient = new ExportIngredient();
        $exportIngredient->setType(strtolower($dumpIngredient->getType()))
                         ->setName(strtolower($dumpIngredient->getName()))
                         ->setAmount($dumpIngredient->getAmount());
        return $exportIngredient;
    }

    /**
     * Maps the dump product to an export one.
     * @param DumpProduct $dumpProduct
     * @return ExportProduct
     */
    protected function mapProduct(DumpProduct $dumpProduct): ExportProduct
    {
        $exportProduct = new ExportProduct();
        $exportProduct->setType(strtolower($dumpProduct->getType()))
                      ->setName(strtolower($dumpProduct->getName()))
                      ->setAmountMin($dumpProduct->getAmountMin())
                      ->setAmountMax($dumpProduct->getAmountMax())
                      ->setProbability($dumpProduct->getProbability());
        return $exportProduct;
    }

    /**
     * Maps the icon hash to the recipe.
     * @param ExportRecipe $recipe
     * @return string
     */
    protected function mapIconHash(ExportRecipe $recipe): string
    {
        $iconHash = $this->iconParser->getIconId(EntityType::RECIPE, $recipe->getName());

        // If the recipe does not have an own icon, it may fall back to its first product's icon.
        if ($iconHash === '' && count($recipe->getProducts()) > 0) {
            $firstProduct = $recipe->getProducts()[0];
            $iconHash = $this->iconParser->getIconId($firstProduct->getType(), $firstProduct->getName());
        }

        return $iconHash;
    }

    /**
     * Validates the data in the combination as a second parsing step.
     * @param Combination $combination
     */
    public function validate(Combination $combination): void
    {
    }
}
