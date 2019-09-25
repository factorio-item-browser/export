<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Entity\Dump;

/**
 * The recipe written to the dumped data.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class Recipe
{
    /**
     * The name of the recipe.
     * @var string
     */
    protected $name = '';

    /**
     * The localised name of the recipe.
     * @var mixed
     */
    protected $localisedName;

    /**
     * The localised description of the recipe.
     * @var mixed
     */
    protected $localisedDescription;

    /**
     * The crafting time of the recipe.
     * @var float
     */
    protected $craftingTime = 0.;

    /**
     * The crafting category of the recipe.
     * @var string
     */
    protected $craftingCategory = '';

    /**
     * The ingredients to craft the recipe.
     * @var array|Ingredient[]
     */
    protected $ingredients = [];

    /**
     * The products crafted by the recipe.
     * @var array|Product[]
     */
    protected $products = [];

    /**
     * Sets the name of the recipe.
     * @param string $name
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Returns the name of the recipe.
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Sets the localised name of the recipe.
     * @param mixed $localisedName
     * @return $this
     */
    public function setLocalisedName($localisedName): self
    {
        $this->localisedName = $localisedName;
        return $this;
    }

    /**
     * Returns the localised name of the recipe.
     * @return mixed
     */
    public function getLocalisedName()
    {
        return $this->localisedName;
    }

    /**
     * Sets the localised description of the recipe.
     * @param mixed $localisedDescription
     * @return $this
     */
    public function setLocalisedDescription($localisedDescription): self
    {
        $this->localisedDescription = $localisedDescription;
        return $this;
    }

    /**
     * Returns the localised description of the recipe.
     * @return mixed
     */
    public function getLocalisedDescription()
    {
        return $this->localisedDescription;
    }

    /**
     * Sets the crafting time of the recipe.
     * @param float $craftingTime
     * @return $this
     */
    public function setCraftingTime(float $craftingTime): self
    {
        $this->craftingTime = $craftingTime;
        return $this;
    }

    /**
     * Returns the crafting time of the recipe.
     * @return float
     */
    public function getCraftingTime(): float
    {
        return $this->craftingTime;
    }

    /**
     * Sets the crafting category of the recipe.
     * @param string $craftingCategory
     * @return $this
     */
    public function setCraftingCategory(string $craftingCategory): self
    {
        $this->craftingCategory = $craftingCategory;
        return $this;
    }

    /**
     * Returns the crafting category of the recipe.
     * @return string
     */
    public function getCraftingCategory(): string
    {
        return $this->craftingCategory;
    }

    /**
     * Sets the ingredients to craft the recipe.
     * @param array|Ingredient[] $ingredients
     * @return $this
     */
    public function setIngredients(array $ingredients): self
    {
        $this->ingredients = $ingredients;
        return $this;
    }

    /**
     * Returns the ingredients to craft the recipe.
     * @return array|Ingredient[]
     */
    public function getIngredients(): array
    {
        return $this->ingredients;
    }

    /**
     * Sets the products crafted by the recipe.
     * @param array|Product[] $products
     * @return $this
     */
    public function setProducts(array $products): self
    {
        $this->products = $products;
        return $this;
    }

    /**
     * Returns the products crafted by the recipe.
     * @return array|Product[]
     */
    public function getProducts(): array
    {
        return $this->products;
    }
}
