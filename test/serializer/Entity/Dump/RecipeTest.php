<?php

declare(strict_types=1);

namespace FactorioItemBrowserTestSerializer\Export\Entity\Dump;

use FactorioItemBrowser\Export\Entity\Dump\Ingredient;
use FactorioItemBrowser\Export\Entity\Dump\Product;
use FactorioItemBrowser\Export\Entity\Dump\Recipe;
use FactorioItemBrowserTestSerializer\Export\SerializerTestCase;

/**
 * The PHPUnit test of serializing the Recipe class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversNothing
 */
class RecipeTest extends SerializerTestCase
{
    /**
     * Returns the object to be serialized or deserialized.
     * @return object
     */
    protected function getObject(): object
    {
        $ingredient1 = new Ingredient();
        $ingredient1->name = 'mno';
        $ingredient2 = new Ingredient();
        $ingredient2->name = 'pqr';

        $product1 = new Product();
        $product1->name = 'stu';
        $product2 = new Product();
        $product2->name = 'vwx';

        $result = new Recipe();
        $result->name = 'abc';
        $result->localisedName = 'def';
        $result->localisedDescription = ['ghi'];
        $result->craftingCategory = 'jkl';
        $result->craftingTime = 13.37;
        $result->ingredients = [$ingredient1, $ingredient2];
        $result->products = [$product1, $product2];
        return $result;
    }

    /**
     * Returns the serialized data.
     * @return array<mixed>
     */
    protected function getData(): array
    {
        return [
            'name' => 'abc',
            'localised_name' => 'def',
            'localised_description' => ['ghi'],
            'crafting_category' => 'jkl',
            'crafting_time' => 13.37,
            'ingredients' => [
                [
                    'name' => 'mno',
                ],
                [
                    'name' => 'pqr',
                ],
            ],
            'products' => [
                [
                    'name' => 'stu',
                ],
                [
                    'name' => 'vwx',
                ],
            ],
        ];
    }
}
