<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Parser;

use BluePsyduck\Common\Data\DataContainer;
use FactorioItemBrowser\Export\I18n\Translator;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;
use FactorioItemBrowser\ExportData\Entity\Recipe;
use FactorioItemBrowser\ExportData\Entity\Recipe\Ingredient;
use FactorioItemBrowser\ExportData\Entity\Recipe\Product;
use FactorioItemBrowser\ExportData\Registry\EntityRegistry;

/**
 * The class parsing the recipes of the dump.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class RecipeParser implements ParserInterface
{
    /**
     * The recipe registry.
     * @var EntityRegistry
     */
    protected $recipeRegistry;

    /**
     * The translator.
     * @var Translator
     */
    protected $translator;

    /**
     * Initializes the parser.
     * @param EntityRegistry $recipeRegistry
     * @param Translator $translator
     */
    public function __construct(EntityRegistry $recipeRegistry, Translator $translator)
    {
        $this->recipeRegistry = $recipeRegistry;
        $this->translator = $translator;
    }
    
    /**
     * Parses the dump data into the combination.
     * @param Combination $combination
     * @param DataContainer $dumpData
     */
    public function parse(Combination $combination, DataContainer $dumpData): void
    {
        foreach ($dumpData->getObjectArray(['recipes', 'normal']) as $recipeData) {
            $recipe = $this->parseRecipe($recipeData, 'normal');
            $combination->addRecipeHash($this->recipeRegistry->set($recipe));
        }
        foreach ($dumpData->getObjectArray(['recipes', 'expensive']) as $recipeData) {
            $recipe = $this->parseRecipe($recipeData, 'expensive');
            $combination->addRecipeHash($this->recipeRegistry->set($recipe));
        }
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
        $recipe->setName(strtolower($recipeData->getString('name')))
               ->setMode($mode)
               ->setCraftingTime($recipeData->getFloat('craftingTime'))
               ->setCraftingCategory($recipeData->getString('craftingCategory'));

        $this->translator->addTranslationsToEntity(
            $recipe->getLabels(),
            'name',
            $recipeData->get(['localised', 'name'])
        );
        $this->translator->addTranslationsToEntity(
            $recipe->getDescriptions(),
            'description',
            $recipeData->get(['localised', 'description'])
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
        $ingredient->setType($ingredientData->getString('type'))
                   ->setName(strtolower($ingredientData->getString('name')))
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
        $product->setType($productData->getString('type'))
                ->setName(strtolower($productData->getString('name')))
                ->setAmountMin($productData->getFloat('amountMin'))
                ->setAmountMax($productData->getFloat('amountMax'))
                ->setProbability($productData->getFloat('probability'))
                ->setOrder($order);

        $amount = ($product->getAmountMin() + $product->getAmountMax()) / 2 * $product->getProbability();
        return ($amount > 0) ? $product : null;
    }
}
