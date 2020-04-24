<?php

declare(strict_types=1);

namespace FactorioItemBrowserTestSerializer\Export\Entity\Dump;

use FactorioItemBrowser\Export\Entity\Dump\DataStage;
use FactorioItemBrowser\Export\Entity\Dump\Icon;
use FactorioItemBrowserTestSerializer\Export\SerializerTestCase;

/**
 * The PHPUnit test of serializing the DataStage class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversNothing
 */
class DataStageTest extends SerializerTestCase
{
    /**
     * Returns the object to be serialized or deserialized.
     * @return object
     */
    protected function getObject(): object
    {
        $icon1 = new Icon();
        $icon1->setName('abc');
        $icon2 = new Icon();
        $icon2->setName('def');

        $result = new DataStage();
        $result->setIcons([$icon1, $icon2]);
        return $result;
    }

    /**
     * Returns the serialized data.
     * @return array<mixed>
     */
    protected function getData(): array
    {
        return [
            'icons' => [
                [
                    'name' => 'abc',
                ],
                [
                    'name' => 'def',
                ],
            ],
        ];
    }
}
