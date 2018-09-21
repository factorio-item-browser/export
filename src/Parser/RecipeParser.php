<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Parser;

use BluePsyduck\Common\Data\DataContainer;
use FactorioItemBrowser\Common\Constant\EntityType;
use FactorioItemBrowser\Common\Constant\RecipeMode;
use FactorioItemBrowser\Export\I18n\Translator;
use FactorioItemBrowser\Export\Utils\LocalisedStringUtils;
use FactorioItemBrowser\ExportData\Entity\LocalisedString;
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
     * The item parser.
     * @var ItemParser
     */
    protected $itemParser;

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
     * The parsed recipes.
     * @var array|Recipe[]
     */
    protected $parsedRecipes = [];

    /**
     * Initializes the parser.
     * @param IconParser $iconParser
     * @param ItemParser $itemParser
     * @param EntityRegistry $recipeRegistry
     * @param Translator $translator
     */
    public function __construct(
        IconParser $iconParser,
        ItemParser $itemParser,
        EntityRegistry $recipeRegistry,
        Translator $translator
    ) {
        $this->iconParser = $iconParser;
        $this->itemParser = $itemParser;
        $this->recipeRegistry = $recipeRegistry;
        $this->translator = $translator;
    }

    /**
     * Parses the data from the dump into actual entities.
     * @param DataContainer $dumpData
     */
    public function parse(DataContainer $dumpData): void
    {
        $this->parsedRecipes = [];
        foreach ($dumpData->getObjectArray(['recipes', 'normal']) as $recipeData) {
            $recipe = $this->parseRecipe($recipeData, RecipeMode::NORMAL);
            $this->parsedRecipes[$recipe->getIdentifier()] = $recipe;
        }
        foreach ($dumpData->getObjectArray(['recipes', 'expensive']) as $recipeData) {
            $recipe = $this->parseRecipe($recipeData, RecipeMode::EXPENSIVE);
            $this->parsedRecipes[$recipe->getIdentifier()] = $recipe;
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
               ->setCraftingCategory($recipeData->getString('craftingCategory'))
               ->setIngredients($this->parseIngredients($recipeData))
               ->setProducts($this->parseProducts($recipeData));

        $this->addTranslations($recipe, $recipeData);
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
     * Checks the parsed data.
     */
    public function check(): void
    {
        $this->parsedRecipes = array_filter($this->parsedRecipes, [$this, 'isUniqueRecipe']);
        foreach ($this->parsedRecipes as $recipe) {
            $this->checkIcon($recipe);
            $this->checkTranslation($recipe);
        }
    }

    /**
     * Checks whether the specified recipe is unique and not a duplication.
     * @param Recipe $recipe
     * @return bool
     */
    protected function isUniqueRecipe(Recipe $recipe): bool
    {
        $result = true;
        if ($recipe->getMode() === RecipeMode::EXPENSIVE) {
            $normalRecipe = $this->findRecipeWithMode($recipe, RecipeMode::NORMAL);
            $result = !$normalRecipe instanceof Recipe
                || $this->calculateRecipeDataHash($recipe) !== $this->calculateRecipeDataHash($normalRecipe);
        }
        return $result;
    }

    /**
     * Returns the recipe variant with the specified mode, if available.
     * @param Recipe $recipe
     * @param string $mode
     * @return Recipe|null
     */
    protected function findRecipeWithMode(Recipe $recipe, string $mode): ?Recipe
    {
        $clonedRecipe = clone($recipe);
        $clonedRecipe->setMode($mode);
        return $this->parsedRecipes[$clonedRecipe->getIdentifier()] ?? null;
    }

    /**
     * Calculates the data hash of the recipe, ignoring the mode.
     * @param Recipe $recipe
     * @return string
     */
    protected function calculateRecipeDataHash(Recipe $recipe): string
    {
        $clonedRecipe = clone($recipe);
        $clonedRecipe->setMode('');
        return $clonedRecipe->calculateHash();
    }

    /**
     * Checks the icon of the recipe.
     * @param Recipe $recipe
     */
    protected function checkIcon(Recipe $recipe): void
    {
        $iconHash = $this->iconParser->getIconHashForEntity(EntityType::RECIPE, $recipe->getName());
        $products = $recipe->getProducts();
        $firstProduct = reset($products);
        if ($iconHash === null && $firstProduct instanceof Product) {
            $iconHash = $this->iconParser->getIconHashForEntity($firstProduct->getType(), $firstProduct->getName());
        }
        if ($iconHash !== null) {
            $recipe->setIconHash($iconHash);
        }
    }

    /**
     * Checks the translation of the recipe.
     * @param Recipe $recipe
     */
    protected function checkTranslation(Recipe $recipe): void
    {
        foreach ($this->itemParser->getItemsWithName($recipe->getName()) as $item) {
            if (LocalisedStringUtils::areEqual($recipe->getLabels(), $item->getLabels())
                && LocalisedStringUtils::areEqual($recipe->getDescriptions(), $item->getDescriptions())
            ) {
                $recipe->setLabels(new LocalisedString())
                       ->setDescriptions(new LocalisedString());
                $item->setProvidesRecipeLocalisation(true);
            }
        }
    }

    /**
     * Persists the parsed data into the combination.
     * @param Combination $combination
     */
    public function persist(Combination $combination): void
    {
        $recipeHashes = [];
        foreach ($this->parsedRecipes as $recipe) {
            $recipeHashes[] = $this->recipeRegistry->set($recipe);
        }
        $combination->setRecipeHashes($recipeHashes);
    }
}
