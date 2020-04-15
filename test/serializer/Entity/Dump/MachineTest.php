<?php

declare(strict_types=1);

namespace FactorioItemBrowserTestSerializer\Export\Entity\Dump;

use FactorioItemBrowser\Export\Entity\Dump\Machine;
use FactorioItemBrowserTestAsset\Export\SerializerTestCase;

/**
 * The PHPUnit test of serializing the Machine class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversNothing
 */
class MachineTest extends SerializerTestCase
{
    /**
     * Returns the object to be serialized or deserialized.
     * @return object
     */
    protected function getObject(): object
    {
        $result = new Machine();
        $result->setName('abc')
               ->setLocalisedName('def')
               ->setLocalisedDescription(['ghi'])
               ->setCraftingCategories(['jkl', 'mno'])
               ->setCraftingSpeed(13.37)
               ->setItemSlots(12)
               ->setFluidInputSlots(23)
               ->setFluidOutputSlots(34)
               ->setModuleSlots(45)
               ->setEnergyUsage(73.31);
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
            'crafting_categories' => ['jkl', 'mno'],
            'crafting_speed' => 13.37,
            'item_slots' => 12,
            'fluid_input_slots' => 23,
            'fluid_output_slots' => 34,
            'module_slots' => 45,
            'energy_usage' => 73.31,
        ];
    }
}
