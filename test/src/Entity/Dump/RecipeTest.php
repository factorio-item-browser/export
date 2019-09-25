<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Entity\Dump;

use FactorioItemBrowser\Export\Entity\Dump\Ingredient;
use FactorioItemBrowser\Export\Entity\Dump\Product;
use FactorioItemBrowser\Export\Entity\Dump\Recipe;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the Recipe class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Entity\Dump\Recipe
 */
class RecipeTest extends TestCase
{
    /**
     * Tests the constructing.
     * @coversNothing
     */
    public function testConstruct(): void
    {
        $entity = new Recipe();

        $this->assertSame('', $entity->getName());
        $this->assertNull($entity->getLocalisedName());
        $this->assertNull($entity->getLocalisedDescription());
    }

    /**
     * Tests the setting and getting the name.
     * @covers ::getName
     * @covers ::setName
     */
    public function testSetAndGetName(): void
    {
        $name = 'abc';
        $entity = new Recipe();

        $this->assertSame($entity, $entity->setName($name));
        $this->assertSame($name, $entity->getName());
    }

    /**
     * Tests the setting and getting the localised name.
     * @covers ::getLocalisedName
     * @covers ::setLocalisedName
     */
    public function testSetAndGetLocalisedName(): void
    {
        $localisedName = ['abc'];
        $entity = new Recipe();

        $this->assertSame($entity, $entity->setLocalisedName($localisedName));
        $this->assertSame($localisedName, $entity->getLocalisedName());
    }

    /**
     * Tests the setting and getting the localised description.
     * @covers ::getLocalisedDescription
     * @covers ::setLocalisedDescription
     */
    public function testSetAndGetLocalisedDescription(): void
    {
        $localisedDescription = ['abc'];
        $entity = new Recipe();

        $this->assertSame($entity, $entity->setLocalisedDescription($localisedDescription));
        $this->assertSame($localisedDescription, $entity->getLocalisedDescription());
    }

    /**
     * Tests the setting and getting the crafting category.
     * @covers ::getCraftingCategory
     * @covers ::setCraftingCategory
     */
    public function testSetAndGetCraftingCategory(): void
    {
        $craftingCategory = 'abc';
        $entity = new Recipe();

        $this->assertSame($entity, $entity->setCraftingCategory($craftingCategory));
        $this->assertSame($craftingCategory, $entity->getCraftingCategory());
    }

    /**
     * Tests the setting and getting the crafting time.
     * @covers ::getCraftingTime
     * @covers ::setCraftingTime
     */
    public function testSetAndGetCraftingTime(): void
    {
        $craftingTime = 13.37;
        $entity = new Recipe();

        $this->assertSame($entity, $entity->setCraftingTime($craftingTime));
        $this->assertSame($craftingTime, $entity->getCraftingTime());
    }

    /**
     * Tests the setting and getting the ingredients.
     * @covers ::getIngredients
     * @covers ::setIngredients
     */
    public function testSetAndGetIngredients(): void
    {
        $ingredients = [
            $this->createMock(Ingredient::class),
            $this->createMock(Ingredient::class),
        ];
        $entity = new Recipe();

        $this->assertSame($entity, $entity->setIngredients($ingredients));
        $this->assertSame($ingredients, $entity->getIngredients());
    }

    /**
     * Tests the setting and getting the products.
     * @covers ::getProducts
     * @covers ::setProducts
     */
    public function testSetAndGetProducts(): void
    {
        $products = [
            $this->createMock(Product::class),
            $this->createMock(Product::class),
        ];
        $entity = new Recipe();

        $this->assertSame($entity, $entity->setProducts($products));
        $this->assertSame($products, $entity->getProducts());
    }
}
