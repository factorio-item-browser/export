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
     * The icon parser.
     * @var IconParser
     */
    protected $iconParser;

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
     * @param IconParser $iconParser
     * @param EntityRegistry $recipeRegistry
     * @param Translator $translator
     */
    public function __construct(IconParser $iconParser, EntityRegistry $recipeRegistry, Translator $translator)
    {
        $this->iconParser = $iconParser;
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
        $combination->setRecipeHashes([]);
        foreach ($dumpData->getObjectArray(['recipes', 'normal']) as $recipeData) {
            $this->processRecipe($combination, $recipeData, 'normal');
        }
        foreach ($dumpData->getObjectArray(['recipes', 'expensive']) as $recipeData) {
            $this->processRecipe($combination, $recipeData, 'expensive');
        }
    }

    /**
     * Processes the specified recipe data.
     * @param Combination $combination
     * @param DataContainer $recipeData
     * @param string $mode
     */
    protected function processRecipe(Combination $combination, DataContainer $recipeData, string $mode): void
    {
        $recipe = $this->parseRecipe($recipeData, $mode);
        $this->addTranslations($recipe, $recipeData);
        $this->assignIconHash($combination, $recipe);
        $combination->addRecipeHash($this->recipeRegistry->set($recipe));
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
               ->setCraftingCategory($recipeData->getString('craftingCategory'))
               ->setIngredients($this->parseIngredients($recipeData))
               ->setProducts($this->parseProducts($recipeData));

        return $recipe;
    }

    /**
     * Parses the ingredients of the recipe.
     * @param DataContainer $recipeData
     * @return array|Ingredient[]
     */
    protected function parseIngredients(DataContainer $recipeData): array
    {
        $ingredients = [];
        $order = 1;
        foreach ($recipeData->getObjectArray('ingredients') as $ingredientData) {
            $ingredient = $this->parseIngredient($ingredientData);
            if ($ingredient instanceof Ingredient) {
                $ingredient->setOrder($order);
                ++$order;

                $ingredients[] = $ingredient;
            }
        }
        return $ingredients;
    }

    /**
     * Parses the ingredient data into an entity.
     * @param DataContainer $ingredientData
     * @return Ingredient|null
     */
    protected function parseIngredient(DataContainer $ingredientData): ?Ingredient
    {
        $ingredient = new Ingredient();
        $ingredient->setType($ingredientData->getString('type'))
                   ->setName(strtolower($ingredientData->getString('name')))
                   ->setAmount($ingredientData->getFloat('amount'));

        return ($ingredient->getAmount() > 0) ? $ingredient : null;
    }
    
    /**
     * Parses the products of the recipe.
     * @param DataContainer $recipeData
     * @return array|Product[]
     */
    protected function parseProducts(DataContainer $recipeData): array
    {
        $products = [];
        $order = 1;
        foreach ($recipeData->getObjectArray('products') as $productData) {
            $product = $this->parseProduct($productData);
            if ($product instanceof Product) {
                $product->setOrder($order);
                ++$order;

                $products[] = $product;
            }
        }
        return $products;
    }

    /**
     * Parses the product data into an entity.
     * @param DataContainer $productData
     * @return Product|null
     */
    protected function parseProduct(DataContainer $productData): ?Product
    {
        $product = new Product();
        $product->setType($productData->getString('type'))
                ->setName(strtolower($productData->getString('name')))
                ->setAmountMin($productData->getFloat('amountMin'))
                ->setAmountMax($productData->getFloat('amountMax'))
                ->setProbability($productData->getFloat('probability'));

        $amount = ($product->getAmountMin() + $product->getAmountMax()) / 2 * $product->getProbability();
        return ($amount > 0) ? $product : null;
    }

    /**
     * Adds the translations to the recipe.
     * @param Recipe $recipe
     * @param DataContainer $recipeData
     */
    protected function addTranslations(Recipe $recipe, DataContainer $recipeData): void
    {
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
    }

    /**
     * Assigns the icon hash to the specified recipe.
     * @param Combination $combination
     * @param Recipe $recipe
     */
    protected function assignIconHash(Combination $combination, Recipe $recipe): void
    {
        $iconHash = $this->iconParser->getIconHashForEntity($combination, 'recipe', $recipe->getName());
        $products = $recipe->getProducts();
        $firstProduct = reset($products);
        if ($iconHash === null && $firstProduct instanceof Product) {
            $iconHash = $this->iconParser->getIconHashForEntity(
                $combination,
                $firstProduct->getType(),
                $firstProduct->getName()
            );
        }
        if ($iconHash !== null) {
            $recipe->setIconHash($iconHash);
        }
    }
}
