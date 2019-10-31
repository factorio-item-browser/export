<?php

declare(strict_types=1);

namespace FactorioItemBrowserTestSerializer\Export\Entity\Dump;

use FactorioItemBrowser\Export\Entity\Dump\Ingredient;
use FactorioItemBrowser\Export\Entity\Dump\Product;
use FactorioItemBrowser\Export\Entity\Dump\Recipe;
use FactorioItemBrowserTestAsset\Export\SerializerTestCase;

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
        $ingredient1->setName('mno');
        $ingredient2 = new Ingredient();
        $ingredient2->setName('pqr');

        $product1 = new Product();
        $product1->setName('stu');
        $product2 = new Product();
        $product2->setName('vwx');

        $result = new Recipe();
        $result->setName('abc')
               ->setLocalisedName('def')
               ->setLocalisedDescription(['ghi'])
               ->setCraftingCategory('jkl')
               ->setCraftingTime(13.37)
               ->setIngredients([$ingredient1, $ingredient2])
               ->setProducts([$product1, $product2]);
        return $result;
    }

    /**
     * Returns the serialized data.
     * @return array
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
