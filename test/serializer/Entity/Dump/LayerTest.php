<?php

declare(strict_types=1);

namespace FactorioItemBrowserTestSerializer\Export\Entity\Dump;

use FactorioItemBrowser\Export\Entity\Dump\Layer;
use FactorioItemBrowserTestSerializer\Export\SerializerTestCase;

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
        $result->file = 'abc';
        $result->size = 1337;
        $result->shiftX = 42;
        $result->shiftY = 21;
        $result->scale = 12.34;
        $result->tintRed = 23.45;
        $result->tintGreen = 34.56;
        $result->tintBlue = 45.67;
        $result->tintAlpha = 56.78;
        return $result;
    }

    /**
     * Returns the serialized data.
     * @return array<mixed>
     */
    protected function getData(): array
    {
        return [
            'file' => 'abc',
            'size' => 1337,
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
