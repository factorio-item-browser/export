<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Mapper;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Export\Entity\Dump\Layer as DumpLayer;
use FactorioItemBrowser\Export\Mapper\IconLayerMapper;
use FactorioItemBrowser\ExportData\Entity\Icon\Layer as ExportLayer;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the IconLayerMapper class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Mapper\IconLayerMapper
 */
class IconLayerMapperTest extends TestCase
{
    use ReflectionTrait;

    /**
     * @covers ::getSupportedDestinationClass
     * @covers ::getSupportedSourceClass
     */
    public function testGetSupportedClasses(): void
    {
        $instance = new IconLayerMapper();

        $this->assertSame(DumpLayer::class, $instance->getSupportedSourceClass());
        $this->assertSame(ExportLayer::class, $instance->getSupportedDestinationClass());
    }

    /**
     * @covers ::map
     */
    public function testMap(): void
    {
        $source = new DumpLayer();
        $source->file = 'abc';
        $source->size = 1337;
        $source->shiftX = 42;
        $source->shiftY = 21;
        $source->scale = 12.34;
        $source->tintRed = 0.45;
        $source->tintGreen = 0.56;
        $source->tintBlue = 0.67;
        $source->tintAlpha = 0.78;

        $expectedDestination = new ExportLayer();
        $expectedDestination->fileName = 'abc';
        $expectedDestination->size = 1337;
        $expectedDestination->offset->x = 42;
        $expectedDestination->offset->y = 21;
        $expectedDestination->scale = 12.34;
        $expectedDestination->tint->red = 0.45;
        $expectedDestination->tint->green = 0.56;
        $expectedDestination->tint->blue = 0.67;
        $expectedDestination->tint->alpha = 0.78;

        $destination = new ExportLayer();

        $instance = new IconLayerMapper();
        $instance->map($source, $destination);

        $this->assertEquals($expectedDestination, $destination);
    }

    /**
     * @return array<mixed>
     */
    public function provideConvertColorValue(): array
    {
        return [
            [0., 0.],
            [0.25, 0.25],
            [1., 1.],
            [127., 127. / 255.],
            [255., 1.],
        ];
    }

    /**
     * @param float $value
     * @param float $expectedValue
     * @throws ReflectionException
     * @covers ::convertColorValue
     * @dataProvider provideConvertColorValue
     */
    public function testConvertColorValue(float $value, float $expectedValue): void
    {
        $instance = new IconLayerMapper();
        $result = $this->invokeMethod($instance, 'convertColorValue', $value);

        $this->assertSame($expectedValue, $result);
    }
}
