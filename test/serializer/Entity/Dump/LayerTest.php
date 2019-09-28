<?php

declare(strict_types=1);

namespace FactorioItemBrowserTestSerializer\Export\Entity\Dump;

use FactorioItemBrowser\Export\Entity\Dump\Layer;
use FactorioItemBrowserTestAsset\Export\SerializerTestCase;

/**
 * The PHPUnit test of serializing the Layer class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversNothing
 */
class LayerTest extends SerializerTestCase
{
    /**
     * Returns the object to be serialized or deserialized.
     * @return object
     */
    protected function getObject(): object
    {
        $result = new Layer();
        $result->setFile('abc')
               ->setShiftX(42)
               ->setShiftY(21)
               ->setScale(12.34)
               ->setTintRed(23.45)
               ->setTintGreen(34.56)
               ->setTintBlue(45.67)
               ->setTintAlpha(56.78);
        return $result;
    }

    /**
     * Returns the serialized data.
     * @return array
     */
    protected function getData(): array
    {
        return [
            'file' => 'abc',
            'shift_x' => 42,
            'shift_y' => 21,
            'scale' => 12.34,
            'tint_red' => 23.45,
            'tint_green' => 34.56,
            'tint_blue' => 45.67,
            'tint_alpha' => 56.78,
        ];
    }
}
