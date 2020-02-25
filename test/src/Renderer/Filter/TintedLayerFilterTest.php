<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Renderer\Filter;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Export\Renderer\Filter\TintedLayerFilter;
use Imagine\Image\ImageInterface;
use Imagine\Image\Palette\Color\ColorInterface;
use Imagine\Image\Palette\PaletteInterface;
use Imagine\Image\Point;
use Imagine\Image\PointInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the TintedLayerFilter class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Renderer\Filter\TintedLayerFilter
 */
class TintedLayerFilterTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked layer image.
     * @var ImageInterface&MockObject
     */
    protected $layerImage;

    /**
     * The mocked tint color.
     * @var ColorInterface&MockObject
     */
    protected $tintColor;

    /**
     * Sets up the test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->layerImage = $this->createMock(ImageInterface::class);
        $this->tintColor = $this->createMock(ColorInterface::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $filter = new TintedLayerFilter($this->layerImage, $this->tintColor);

        $this->assertSame($this->layerImage, $this->extractProperty($filter, 'layerImage'));
        $this->assertSame($this->tintColor, $this->extractProperty($filter, 'tintColor'));
    }

    /**
     * Tests the apply method.
     * @throws ReflectionException
     * @covers ::apply
     */
    public function testApply(): void
    {
        /* @var TintedLayerFilter&MockObject $filter */
        $filter = $this->getMockBuilder(TintedLayerFilter::class)
                       ->onlyMethods(['hasTint'])
                       ->setConstructorArgs([$this->layerImage, $this->tintColor])
                       ->getMock();
        $filter->expects($this->once())
               ->method('hasTint')
               ->willReturn(true);

        /* @var ImageInterface&MockObject $image */
        $image = $this->createMock(ImageInterface::class);
        $image->expects($this->once())
              ->method('fill')
              ->with($filter);

        $result = $filter->apply($image);

        $this->assertSame($image, $result);
        $this->assertSame($image, $this->extractProperty($filter, 'image'));
    }

    /**
     * Tests the apply method.
     * @covers ::apply
     */
    public function testApplyWithoutTint(): void
    {
        $expectedPoint = new Point(0, 0);

        /* @var ImageInterface&MockObject $image */
        $image = $this->createMock(ImageInterface::class);
        $image->expects($this->once())
              ->method('paste')
              ->with($this->identicalTo($this->layerImage), $this->equalTo($expectedPoint))
              ->willReturnSelf();
        $image->expects($this->never())
              ->method('fill');

        /* @var TintedLayerFilter&MockObject $filter */
        $filter = $this->getMockBuilder(TintedLayerFilter::class)
                       ->onlyMethods(['hasTint'])
                       ->setConstructorArgs([$this->layerImage, $this->tintColor])
                       ->getMock();
        $filter->expects($this->once())
               ->method('hasTint')
               ->willReturn(false);

        $result = $filter->apply($image);

        $this->assertSame($image, $result);
    }

    /**
     * Provides the data for the hasTint test.
     * @return array<mixed>
     */
    public function provideHasTint(): array
    {
        return [
            [255, 255, 255, 100, false],
            [0, 255, 255, 100, true],
            [255, 0, 255, 100, true],
            [255, 255, 0, 100, true],
            [255, 255, 255, 0, true],
        ];
    }

    /**
     * Tests the hasTint method.
     * @param int $red
     * @param int $green
     * @param int $blue
     * @param int $alpha
     * @param bool $expectedResult
     * @throws ReflectionException
     * @covers ::hasTint
     * @dataProvider provideHasTint
     */
    public function testHasTint(int $red, int $green, int $blue, int $alpha, bool $expectedResult): void
    {
        $this->tintColor->expects($this->any())
                        ->method('getValue')
                        ->willReturnMap([
                            [ColorInterface::COLOR_RED, $red],
                            [ColorInterface::COLOR_GREEN, $green],
                            [ColorInterface::COLOR_BLUE, $blue],
                        ]);

        $this->tintColor->expects($this->any())
                        ->method('getAlpha')
                        ->willReturn($alpha);

        $filter = new TintedLayerFilter($this->layerImage, $this->tintColor);
        $result = $this->invokeMethod($filter, 'hasTint');

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the getColor method.
     * @throws ReflectionException
     * @covers ::getColor
     */
    public function testGetColor(): void
    {
        /* @var PointInterface&MockObject $point */
        $point = $this->createMock(PointInterface::class);
        /* @var ColorInterface&MockObject $imageColor */
        $imageColor = $this->createMock(ColorInterface::class);
        /* @var ColorInterface&MockObject $layerColor */
        $layerColor = $this->createMock(ColorInterface::class);
        /* @var ColorInterface&MockObject $color */
        $color = $this->createMock(ColorInterface::class);

        /* @var PaletteInterface&MockObject $palette */
        $palette = $this->createMock(PaletteInterface::class);
        $palette->expects($this->once())
                ->method('color')
                ->with([12, 23, 34], 45)
                ->willReturn($color);

        /* @var ImageInterface&MockObject $image */
        $image = $this->createMock(ImageInterface::class);
        $image->expects($this->once())
              ->method('getColorAt')
              ->with($point)
              ->willReturn($imageColor);
        $image->expects($this->once())
              ->method('palette')
              ->willReturn($palette);

        $this->layerImage->expects($this->once())
                         ->method('getColorAt')
                         ->with($point)
                         ->willReturn($layerColor);

        /* @var TintedLayerFilter&MockObject $filter */
        $filter = $this->getMockBuilder(TintedLayerFilter::class)
                       ->onlyMethods(['calculateComponent', 'calculateAlpha'])
                       ->setConstructorArgs([$this->layerImage, $this->tintColor])
                       ->getMock();
        $filter->expects($this->exactly(3))
               ->method('calculateComponent')
               ->withConsecutive(
                   [ColorInterface::COLOR_RED, $layerColor, $imageColor],
                   [ColorInterface::COLOR_GREEN, $layerColor, $imageColor],
                   [ColorInterface::COLOR_BLUE, $layerColor, $imageColor]
               )
               ->willReturnOnConsecutiveCalls(
                   12,
                   23,
                   34
               );
        $filter->expects($this->once())
               ->method('calculateAlpha')
               ->with($layerColor, $imageColor)
               ->willReturn(45);
        $this->injectProperty($filter, 'image', $image);

        $result = $filter->getColor($point);
        $this->assertSame($color, $result);
    }

    /**
     * Provides the data for the calculateComponent test.
     * @return array<mixed>
     */
    public function provideCalculateComponent(): array
    {
        return [
            [35, 45, 55, 65, 75, 85, 20],
            [35, 45, 255, 100, 0, 0, 35],  // Only source color with full tint.
            [35, 45, 255, 0, 100, 0, 45],  // Only dest color with full tint.
        ];
    }

    /**
     * Tests the calculateComponent method.
     * @param int $colorSource
     * @param int $colorDest
     * @param int $colorTint
     * @param int $alphaSource
     * @param int $alphaDest
     * @param int $alphaTint
     * @param int $expectedResult
     * @throws ReflectionException
     * @covers ::calculateComponent
     * @dataProvider provideCalculateComponent
     */
    public function testCalculateComponent(
        int $colorSource,
        int $colorDest,
        int $colorTint,
        int $alphaSource,
        int $alphaDest,
        int $alphaTint,
        int $expectedResult
    ): void {
        $component = 'foo';

        /* @var ColorInterface&MockObject $source */
        $source = $this->createMock(ColorInterface::class);
        $source->expects($this->once())
               ->method('getValue')
               ->with($component)
               ->willReturn($colorSource);
        $source->expects($this->once())
               ->method('getAlpha')
               ->willReturn($alphaSource);

        /* @var ColorInterface&MockObject $dest */
        $dest = $this->createMock(ColorInterface::class);
        $dest->expects($this->once())
             ->method('getValue')
             ->with($component)
             ->willReturn($colorDest);
        $dest->expects($this->once())
             ->method('getAlpha')
             ->willReturn($alphaDest);

        $this->tintColor->expects($this->once())
                        ->method('getValue')
                        ->with($component)
                        ->willReturn($colorTint);
        $this->tintColor->expects($this->once())
                        ->method('getAlpha')
                        ->willReturn($alphaTint);

        $filter = new TintedLayerFilter($this->layerImage, $this->tintColor);
        $result = $this->invokeMethod($filter, 'calculateComponent', $component, $source, $dest);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Provides the data for the calculateAlpha test.
     * @return array<mixed>
     */
    public function provideCalculateAlpha(): array
    {
        return [
            [35, 45, 55, 71], // Actually mixing alpha values
            [35, 0, 55, 35],  // Full source without dest stays as source.
            [0, 45, 55, 45],  // No source will fully apply the dest.
            [65, 75, 0, 100], // Overshooting alpha is capped to 100.
        ];
    }

    /**
     * Tests the calculateAlpha method.
     * @param int $alphaSource
     * @param int $alphaDest
     * @param int $alphaTint
     * @param int $expectedResult
     * @throws ReflectionException
     * @covers ::calculateAlpha
     * @dataProvider provideCalculateAlpha
     */
    public function testCalculateAlpha(int $alphaSource, int $alphaDest, int $alphaTint, int $expectedResult): void
    {
        /* @var ColorInterface&MockObject $source */
        $source = $this->createMock(ColorInterface::class);
        $source->expects($this->once())
               ->method('getAlpha')
               ->willReturn($alphaSource);

        /* @var ColorInterface&MockObject $dest */
        $dest = $this->createMock(ColorInterface::class);
        $dest->expects($this->once())
             ->method('getAlpha')
             ->willReturn($alphaDest);

        $this->tintColor->expects($this->once())
                        ->method('getAlpha')
                        ->willReturn($alphaTint);

        $filter = new TintedLayerFilter($this->layerImage, $this->tintColor);
        $result = $this->invokeMethod($filter, 'calculateAlpha', $source, $dest);

        $this->assertSame($expectedResult, $result);
    }
}
