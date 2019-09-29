<?php

declare(strict_types=1);

namespace FactorioItemBrowserTestSerializer\Export\Entity\Dump;

use FactorioItemBrowser\Export\Entity\Dump\ControlStage;
use FactorioItemBrowser\Export\Entity\Dump\Fluid;
use FactorioItemBrowser\Export\Entity\Dump\Item;
use FactorioItemBrowser\Export\Entity\Dump\Machine;
use FactorioItemBrowser\Export\Entity\Dump\Recipe;
use FactorioItemBrowserTestAsset\Export\SerializerTestCase;

/**
 * The PHPUnit test of serializing the ControlStage class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversNothing
 */
class ControlStageTest extends SerializerTestCase
{
    /**
     * Returns the object to be serialized or deserialized.
     * @return object
     */
    protected function getObject(): object
    {
        $item1 = new Item();
        $item1->setName('abc');
        $item2 = new Item();
        $item2->setName('def');

        $fluid1 = new Fluid();
        $fluid1->setName('ghi');
        $fluid2 = new Fluid();
        $fluid2->setName('jkl');

        $machine1 = new Machine();
        $machine1->setName('mno');
        $machine2 = new Machine();
        $machine2->setName('pqr');

        $recipe1 = new Recipe();
        $recipe1->setName('stu');
        $recipe2 = new Recipe();
        $recipe2->setName('vwx');
        $recipe3 = new Recipe();
        $recipe3->setName('yza');
        $recipe4 = new Recipe();
        $recipe4->setName('bcd');

        $result = new ControlStage();
        $result->setItems([$item1, $item2])
               ->setFluids([$fluid1, $fluid2])
               ->setMachines([$machine1, $machine2])
               ->setNormalRecipes([$recipe1, $recipe2])
               ->setExpensiveRecipes([$recipe3, $recipe4]);
        return $result;
    }

    /**
     * Returns the serialized data.
     * @return array
     */
    protected function getData(): array
    {
        return [
            'items' => [
                [
                    'name' => 'abc',
                ],
                [
                    'name' => 'def',
                ],
            ],
            'fluids' => [
                [
                    'name' => 'ghi',
                ],
                [
                    'name' => 'jkl',
                ],
            ],
            'machines' => [
                [
                    'name' => 'mno',
                ],
                [
                    'name' => 'pqr',
                ],
            ],
            'normal_recipes' => [
                [
                    'name' => 'stu',
                ],
                [
                    'name' => 'vwx',
                ],
            ],
            'expensive_recipes' => [
                [
                    'name' => 'yza',
                ],
                [
                    'name' => 'bcd',
                ],
            ],
        ];
    }
}
