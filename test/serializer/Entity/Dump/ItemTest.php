<?php

declare(strict_types=1);

namespace FactorioItemBrowserTestSerializer\Export\Entity\Dump;

use FactorioItemBrowser\Export\Entity\Dump\Item;
use FactorioItemBrowserTestSerializer\Export\SerializerTestCase;

/**
 * The PHPUnit test of serializing the Item class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversNothing
 */
class ItemTest extends SerializerTestCase
{
    /**
     * Returns the object to be serialized or deserialized.
     * @return object
     */
    protected function getObject(): object
    {
        $result = new Item();
        $result->setName('abc')
               ->setLocalisedName('def')
               ->setLocalisedDescription(['ghi'])
               ->setLocalisedEntityName('jkl')
               ->setLocalisedEntityDescription(['mno']);
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
            'localised_entity_name' => 'jkl',
            'localised_entity_description' => ['mno'],
        ];
    }
}
