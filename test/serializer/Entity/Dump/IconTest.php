<?php

declare(strict_types=1);

namespace FactorioItemBrowserTestSerializer\Export\Entity\Dump;

use FactorioItemBrowser\Export\Entity\Dump\Icon;
use FactorioItemBrowser\Export\Entity\Dump\Layer;
use FactorioItemBrowserTestSerializer\Export\SerializerTestCase;

/**
 * The PHPUnit test of serializing the Icon class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversNothing
 */
class IconTest extends SerializerTestCase
{
    /**
     * Returns the object to be serialized or deserialized.
     * @return object
     */
    protected function getObject(): object
    {
        $layer1 = new Layer();
        $layer1->setFile('abc');
        $layer2 = new Layer();
        $layer2->setFile('def');

        $result = new Icon();
        $result->setType('ghi')
               ->setName('jkl')
               ->setLayers([$layer1, $layer2]);
        return $result;
    }

    /**
     * Returns the serialized data.
     * @return array<mixed>
     */
    protected function getData(): array
    {
        return [
            'type' => 'ghi',
            'name' => 'jkl',
            'layers' => [
                [
                    'file' => 'abc',
                ],
                [
                    'file' => 'def',
                ],
            ],
        ];
    }
}
