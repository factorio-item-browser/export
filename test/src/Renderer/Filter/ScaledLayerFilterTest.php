<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Renderer\Filter;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Export\Renderer\Filter\ScaledLayerFilter;
use FactorioItemBrowser\ExportData\Entity\Icon\Layer;
use FactorioItemBrowser\ExportData\Entity\Icon\Offset;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;
use Imagine\Image\Palette\Color\ColorInterface;
use Imagine\Image\Palette\PaletteInterface;
use Imagine\Image\Point;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the ScaledLayerFilter class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Renderer\Filter\ScaledLayerFilter
 */
class ScaledLayerFilterTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked layer.
     * @var Layer&MockObject
     */
    protected $layer;

    /**
     * Sets up the test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->layer = $this->createMock(Layer::class);
    }

    /**
     * @throws ReflectionException
     * Tests the constructing.
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $size = 42;

        $filter = new ScaledLayerFilter($this->layer, $size);

        $this->assertSame($this->layer, $this->extractProperty($filter, 'layer'));
        $this->assertSame($size, $this->extractProperty($filter, 'size'));
    }

    /**
     * Tests the apply method.
     * @covers ::apply
     */
    public function testApply(): void
    {
        /* @var ImageInterface&MockObject $image1 */
        $image1 = $this->createMock(ImageInterface::class);
        /* @var ImageInterface&MockObject $image2 */
        $image2 = $this->createMock(ImageInterface::class);
        /* @var ImageInterface&MockObject $image3 */
        $image3 = $this->createMock(ImageInterface::class);
        /* @var ImageInterface&MockObject $image4 */
        $image4 = $this->createMock(ImageInterface::class);

        /* @var ScaledLayerFilter&MockObject $filter */
        $filter = $this->getMockBuilder(ScaledLayerFilter::class)
                       ->onlyMethods(['scaleLayer', 'offsetLayer', 'adjustLayer'])
                       ->disableOriginalConstructor()
                       ->getMock();
        $filter->expects($this->once())
               ->method('scaleLayer')
               ->with($image1)
               ->willReturn($image2);
        $filter->expects($this->once())
               ->method('offsetLayer')
               ->with($image2)
               ->willReturn($image3);
        $filter->expects($this->once())
               ->method('adjustLayer')
               ->with($image3)
               ->willReturn($image4);

        $result = $filter->apply($image1);
        $this->assertSame($image4, $result);
    }

    /**
     * Tests the createTemporaryImage method.
     * @throws ReflectionException
     * @covers ::createTemporaryImage
     */
    public function testCreateTemporaryImage(): void
    {
        $size = 42;
        $expectedBox = new Box(42, 42);

        /* @var ColorInterface&MockObject $color */
        $color = $this->createMock(ColorInterface::class);
        /* @var ImageInterface&MockObject $newImage */
        $newImage = $this->createMock(ImageInterface::class);

        /* @var PaletteInterface&MockObject $palette */
        $palette = $this->createMock(PaletteInterface::class);
        $palette->expects($this->once())
                ->method('color')
                ->with($this->identicalTo(0xFFFFFF), 0)
                ->willReturn($color);

        /* @var ImageInterface&MockObject $image */
        $image = $this->createMock(ImageInterface::class);
        $image->expects($this->once())
              ->method('palette')
              ->willReturn($palette);

        /* @var ImagineInterface&MockObject $imagine */
        $imagine = $this->createMock(ImagineInterface::class);
        $imagine->expects($this->once())
                ->method('create')
                ->with($this->equalTo($expectedBox), $this->identicalTo($color))
                ->willReturn($newImage);

        /* @var ScaledLayerFilter&MockObject $filter */
        $filter = $this->getMockBuilder(ScaledLayerFilter::class)
                       ->onlyMethods(['getImagine'])
                       ->setConstructorArgs([$this->layer, 1337])
                       ->getMock();
        $filter->expects($this->once())
               ->method('getImagine')
               ->willReturn($imagine);

        $result = $this->invokeMethod($filter, 'createTemporaryImage', $image, $size);

        $this->assertSame($newImage, $result);
    }

    /**
     * Tests the scaleLayer method.
     * @throws ReflectionException
     * @covers ::scaleLayer
     */
    public function testScaleLayer(): void
    {
        $scale = 4.2;
        $size = new Box(21, 21);
        $expectedBox = new Box(88, 88);

        /* @var ImageInterface&MockObject $image */
        $image = $this->createMock(ImageInterface::class);
        $image->expects($this->once())
              ->method('getSize')
              ->willReturn($size);
        $image->expects($this->once())
              ->method('resize')
              ->with($this->equalTo($expectedBox))
              ->willReturnSelf();

        $this->layer->expects($this->once())
                    ->method('getScale')
                    ->willReturn($scale);

        $filter = new ScaledLayerFilter($this->layer, 1337);
        $result = $this->invokeMethod($filter, 'scaleLayer', $image);

        $this->assertSame($image, $result);
    }

    /**
     * Tests the offsetLayer method.
     * @throws ReflectionException
     * @covers ::offsetLayer
     */
    public function testOffsetLayer(): void
    {
        $offsetX = -21;
        $offsetY = 21;

        $imageSize = new Box(42, 42);
        $expectedPoint = new Point(21, 63);
        $expectedSize = 126;

        /* @var ImageInterface&MockObject $image */
        $image = $this->createMock(ImageInterface::class);
        $image->expects($this->once())
              ->method('getSize')
              ->willReturn($imageSize);

        /* @var ImageInterface&MockObject $offsetImage */
        $offsetImage = $this->createMock(ImageInterface::class);
        $offsetImage->expects($this->once())
                    ->method('paste')
                    ->with($this->identicalTo($image), $this->equalTo($expectedPoint))
                    ->willReturnSelf();

        /* @var Offset&MockObject $offset */
        $offset = $this->createMock(Offset::class);
        $offset->expects($this->any())
               ->method('getX')
               ->willReturn($offsetX);
        $offset->expects($this->any())
               ->method('getY')
               ->willReturn($offsetY);

        $this->layer->expects($this->once())
                    ->method('getOffset')
                    ->willReturn($offset);

        /* @var ScaledLayerFilter&MockObject $filter */
        $filter = $this->getMockBuilder(ScaledLayerFilter::class)
                       ->onlyMethods(['createTemporaryImage'])
                       ->setConstructorArgs([$this->layer, 1337])
                       ->getMock();
        $filter->expects($this->once())
               ->method('createTemporaryImage')
               ->with($this->identicalTo($image), $this->identicalTo($expectedSize))
               ->willReturn($offsetImage);

        $result = $this->invokeMethod($filter, 'offsetLayer', $image);

        $this->assertSame($offsetImage, $result);
    }

    /**
     * Tests the offsetLayer method.
     * @throws ReflectionException
     * @covers ::offsetLayer
     */
    public function testOffsetLayerWithoutOffset(): void
    {
        $offsetX = 0;
        $offsetY = 0;

        /* @var ImageInterface&MockObject $image */
        $image = $this->createMock(ImageInterface::class);

        /* @var Offset&MockObject $offset */
        $offset = $this->createMock(Offset::class);
        $offset->expects($this->any())
               ->method('getX')
               ->willReturn($offsetX);
        $offset->expects($this->any())
               ->method('getY')
               ->willReturn($offsetY);

        $this->layer->expects($this->once())
                    ->method('getOffset')
                    ->willReturn($offset);

        /* @var ScaledLayerFilter&MockObject $filter */
        $filter = $this->getMockBuilder(ScaledLayerFilter::class)
                       ->onlyMethods(['createTemporaryImage'])
                       ->setConstructorArgs([$this->layer, 1337])
                       ->getMock();
        $filter->expects($this->never())
               ->method('createTemporaryImage');

        $result = $this->invokeMethod($filter, 'offsetLayer', $image);

        $this->assertSame($image, $result);
    }

    /**
     * Provides the data for the adjustLayer test.
     * @return array<mixed>
     */
    public function provideAdjustLayer(): array
    {
        return [
            [42, 21, true, false],
            [21, 42, false, true],
            [42, 42, false, false],
        ];
    }

    /**
     * Tests the adjustLayer method.
     * @param int $imageSize
     * @param int $size
     * @param bool $expectCrop
     * @param bool $expectExtend
     * @throws ReflectionException
     * @covers ::adjustLayer
     * @dataProvider provideAdjustLayer
     */
    public function testAdjustLayer(int $imageSize, int $size, bool $expectCrop, bool $expectExtend): void
    {
        /* @var ImageInterface&MockObject $image */
        $image = $this->createMock(ImageInterface::class);
        $image->expects($this->once())
              ->method('getSize')
              ->willReturn(new Box($imageSize, $imageSize));

        /* @var ScaledLayerFilter&MockObject $filter */
        $filter = $this->getMockBuilder(ScaledLayerFilter::class)
                       ->onlyMethods(['cropLayer', 'extendLayer'])
                       ->setConstructorArgs([$this->layer, $size])
                       ->getMock();
        $filter->expects($expectCrop ? $this->once() : $this->never())
               ->method('cropLayer')
               ->with($this->identicalTo($image), $this->identicalTo($size))
               ->willReturn($image);
        $filter->expects($expectExtend ? $this->once() : $this->never())
               ->method('extendLayer')
               ->with($this->identicalTo($image), $this->identicalTo($size))
               ->willReturn($image);

        $result = $this->invokeMethod($filter, 'adjustLayer', $image);

        $this->assertSame($image, $result);
    }

    /**
     * Tests the cropLayer method.
     * @throws ReflectionException
     * @covers ::cropLayer
     */
    public function testCropLayer(): void
    {
        $size = 21;
        $imageSize = new Box(42, 42);
        $expectedPoint = new Point(10, 10);
        $expectedBox = new Box(21, 21);

        /* @var ImageInterface&MockObject $image */
        $image = $this->createMock(ImageInterface::class);
        $image->expects($this->once())
              ->method('getSize')
              ->willReturn($imageSize);
        $image->expects($this->once())
              ->method('crop')
              ->with($this->equalTo($expectedPoint), $this->equalTo($expectedBox))
              ->willReturnSelf();

        $filter = new ScaledLayerFilter($this->layer, 1337);
        $result = $this->invokeMethod($filter, 'cropLayer', $image, $size);

        $this->assertSame($image, $result);
    }

    /**
     * Tests the extendLayer method.
     * @throws ReflectionException
     * @covers ::extendLayer
     */
    public function testExtendLayer(): void
    {
        $size = 42;
        $imageSize = new Box(21, 21);
        $expectedPoint = new Point(10, 10);

        /* @var ImageInterface&MockObject $image */
        $image = $this->createMock(ImageInterface::class);
        $image->expects($this->once())
              ->method('getSize')
              ->willReturn($imageSize);
        
        /* @var ImageInterface&MockObject $newImage */
        $newImage = $this->createMock(ImageInterface::class);
        $newImage->expects($this->once())
                 ->method('paste')
                 ->with($this->identicalTo($image), $this->equalTo($expectedPoint))
                 ->willReturnSelf();
        
        /* @var ScaledLayerFilter&MockObject $filter */
        $filter = $this->getMockBuilder(ScaledLayerFilter::class)
                       ->onlyMethods(['createTemporaryImage'])
                       ->setConstructorArgs([$this->layer, 1337])
                       ->getMock();
        $filter->expects($this->once())
               ->method('createTemporaryImage')
               ->with($this->identicalTo($image), $this->identicalTo($size))
               ->willReturn($newImage);

        $result = $this->invokeMethod($filter, 'extendLayer', $image, $size);

        $this->assertSame($newImage, $result);
    }
}
