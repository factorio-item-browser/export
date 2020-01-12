<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Renderer\Filter;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Export\Renderer\Filter\ScaledLayerFilter;
use FactorioItemBrowser\ExportData\Entity\Icon\Layer;
use Imagine\Image\Box;
use Imagine\Image\BoxInterface;
use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;
use Imagine\Image\Palette\Color\ColorInterface;
use Imagine\Image\Palette\PaletteInterface;
use Imagine\Image\PointInterface;
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
     * @throws ReflectionException
     * Tests the constructing.
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $layer = (new Layer())->setScale(13.37);
        $size = 42;

        $filter = new ScaledLayerFilter($layer, $size);

        $this->assertSame($layer, $this->extractProperty($filter, 'layer'));
        $this->assertSame($size, $this->extractProperty($filter, 'size'));
    }

    /**
     * Tests the apply method.
     * @covers ::apply
     */
    public function testApply(): void
    {
        /* @var ImageInterface $image1 */
        $image1 = $this->createMock(ImageInterface::class);
        /* @var ImageInterface $image2 */
        $image2 = $this->createMock(ImageInterface::class);
        /* @var ImageInterface $image3 */
        $image3 = $this->createMock(ImageInterface::class);
        /* @var ImageInterface $image4 */
        $image4 = $this->createMock(ImageInterface::class);

        /* @var ScaledLayerFilter|MockObject $filter */
        $filter = $this->getMockBuilder(ScaledLayerFilter::class)
                       ->onlyMethods(['scaleLayer', 'offsetLayer', 'cropLayer'])
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
               ->method('cropLayer')
               ->with($image3)
               ->willReturn($image4);

        $result = $filter->apply($image1);
        $this->assertSame($image4, $result);
    }

    /**
     * Provides the data for the scaleLayer test.
     * @return array<mixed>
     */
    public function provideScaleLayer(): array
    {
        return [
            [13.37, true],
            [1., false],
        ];
    }

    /**
     * Tests the scaleLayer method.
     * @param float $layerScale
     * @param bool $expectResize
     * @throws ReflectionException
     * @covers ::scaleLayer
     * @dataProvider provideScaleLayer
     */
    public function testScaleLayer(float $layerScale, bool $expectResize): void
    {
        $layer = (new Layer())->setScale($layerScale);

        /* @var BoxInterface|MockObject $box */
        $box = $this->getMockBuilder(BoxInterface::class)
                    ->onlyMethods(['scale'])
                    ->getMockForAbstractClass();
        $box->expects($expectResize ? $this->once() : $this->never())
            ->method('scale')
            ->with($layerScale)
            ->willReturnSelf();

        /* @var ImageInterface|MockObject $layerImage */
        $layerImage = $this->getMockBuilder(ImageInterface::class)
                           ->onlyMethods(['getSize', 'resize'])
                           ->getMockForAbstractClass();
        $layerImage->expects($expectResize ? $this->once() : $this->never())
                   ->method('getSize')
                   ->willReturn($box);
        $layerImage->expects($expectResize ? $this->once() : $this->never())
                   ->method('resize')
                   ->with($box)
                   ->willReturnSelf();

        $filter = new ScaledLayerFilter($layer, 42);
        $result = $this->invokeMethod($filter, 'scaleLayer', $layerImage);
        $this->assertSame($layerImage, $result);
    }

    /**
     * Tests the offsetLayer method.
     * @throws ReflectionException
     * @covers ::offsetLayer
     */
    public function testOffsetLayer(): void
    {
        /* @var ImageInterface $layerImage */
        $layerImage = $this->createMock(ImageInterface::class);
        /* @var PointInterface $drawPoint */
        $drawPoint = $this->createMock(PointInterface::class);

        /* @var ImageInterface|MockObject $temporaryImage */
        $temporaryImage = $this->getMockBuilder(ImageInterface::class)
                               ->onlyMethods(['paste'])
                               ->getMockForAbstractClass();
        $temporaryImage->expects($this->once())
                       ->method('paste')
                       ->with($layerImage, $drawPoint)
                       ->willReturnSelf();

        /* @var ScaledLayerFilter|MockObject $filter */
        $filter = $this->getMockBuilder(ScaledLayerFilter::class)
                       ->onlyMethods(['createTemporaryImage', 'calculateDrawPoint'])
                       ->disableOriginalConstructor()
                       ->getMock();
        $filter->expects($this->once())
               ->method('createTemporaryImage')
               ->with($layerImage)
               ->willReturn($temporaryImage);
        $filter->expects($this->once())
               ->method('calculateDrawPoint')
               ->with($temporaryImage, $layerImage)
               ->willReturn($drawPoint);

        $result = $this->invokeMethod($filter, 'offsetLayer', $layerImage);
        $this->assertSame($temporaryImage, $result);
    }

    /**
     * Tests the cropLayer method.
     * @covers ::cropLayer
     * @throws ReflectionException
     */
    public function testCropLayer(): void
    {
        $size = 12;
        $width = 42;
        $height = 24;
        $imageSize = new Box($width, $height);

        /* @var ImageInterface $croppedImage */
        $croppedImage = $this->createMock(ImageInterface::class);

        /* @var ImageInterface|MockObject $image */
        $image = $this->getMockBuilder(ImageInterface::class)
                      ->onlyMethods(['getSize', 'crop'])
                      ->getMockForAbstractClass();
        $image->expects($this->once())
              ->method('getSize')
              ->willReturn($imageSize);
        $image->expects($this->once())
              ->method('crop')
              ->with(
                  $this->callback(function (PointInterface $point): bool {
                      $this->assertSame(15, $point->getX());
                      $this->assertSame(6, $point->getY());
                      return true;
                  }),
                  $this->callback(function (BoxInterface $box) use ($size): bool {
                      $this->assertSame($size, $box->getWidth());
                      $this->assertSame($size, $box->getHeight());
                      return true;
                  })
              )
              ->willReturn($croppedImage);

        $filter = new ScaledLayerFilter(new Layer(), $size);

        $result = $this->invokeMethod($filter, 'cropLayer', $image);
        $this->assertSame($croppedImage, $result);
    }

    /**
     * Tests the createTemporaryImage method.
     * @throws ReflectionException
     * @covers ::createTemporaryImage
     */
    public function testCreateTemporaryImage(): void
    {
        $size = 42;
        /* @var ColorInterface $color */
        $color = $this->createMock(ColorInterface::class);
        /* @var ImageInterface $image */
        $image = $this->createMock(ImageInterface::class);

        /* @var BoxInterface|MockObject $newSize */
        $newSize = $this->getMockBuilder(BoxInterface::class)
                        ->onlyMethods(['scale', 'increase'])
                        ->getMockForAbstractClass();
        $newSize->expects($this->once())
                ->method('scale')
                ->with(2)
                ->willReturnSelf();
        $newSize->expects($this->once())
                ->method('increase')
                ->with($size)
                ->willReturnSelf();

        /* @var PaletteInterface|MockObject $palette */
        $palette = $this->getMockBuilder(PaletteInterface::class)
                        ->onlyMethods(['color'])
                        ->getMockForAbstractClass();
        $palette->expects($this->once())
                ->method('color')
                ->with(0xFFFFFF, 0)
                ->willReturn($color);

        /* @var ImageInterface|MockObject $layerImage */
        $layerImage = $this->getMockBuilder(ImageInterface::class)
                           ->onlyMethods(['getSize', 'palette'])
                           ->getMockForAbstractClass();
        $layerImage->expects($this->once())
                   ->method('getSize')
                   ->willReturn($newSize);
        $layerImage->expects($this->once())
                   ->method('palette')
                   ->willReturn($palette);

        /* @var ImagineInterface|MockObject $imagine */
        $imagine = $this->getMockBuilder(ImagineInterface::class)
                        ->onlyMethods(['create'])
                        ->getMockForAbstractClass();
        $imagine->expects($this->once())
                ->method('create')
                ->with($newSize, $color)
                ->willReturn($image);

        /* @var ScaledLayerFilter|MockObject $filter */
        $filter = $this->getMockBuilder(ScaledLayerFilter::class)
                       ->onlyMethods(['getImagine'])
                       ->setConstructorArgs([new Layer(), $size])
                       ->getMock();
        $filter->expects($this->once())
               ->method('getImagine')
               ->willReturn($imagine);

        $result = $this->invokeMethod($filter, 'createTemporaryImage', $layerImage);
        $this->assertSame($image, $result);
    }

    /**
     * Tests the calculateDrawPoint method.
     * @throws ReflectionException
     * @covers ::calculateDrawPoint
     */
    public function testCalculateDrawPoint(): void
    {
        $layer = new Layer();
        $layer->setOffsetX(34)
              ->setOffsetY(67);
        
        /* @var ImageInterface|MockObject $temporaryImage */
        $temporaryImage = $this->getMockBuilder(ImageInterface::class)
                               ->onlyMethods(['getSize'])
                               ->getMockForAbstractClass();
        $temporaryImage->expects($this->once())
                       ->method('getSize')
                       ->willReturn(new Box(12, 45));
        /* @var ImageInterface|MockObject $layerImage */
        $layerImage = $this->getMockBuilder(ImageInterface::class)
                           ->onlyMethods(['getSize'])
                           ->getMockForAbstractClass();
        $layerImage->expects($this->once())
                   ->method('getSize')
                   ->willReturn(new Box(23, 56));

        /* @var ScaledLayerFilter|MockObject $filter */
        $filter = $this->getMockBuilder(ScaledLayerFilter::class)
                       ->onlyMethods(['calculateDrawPosition'])
                       ->setConstructorArgs([$layer, 1337])
                       ->getMock();
        $filter->expects($this->exactly(2))
               ->method('calculateDrawPosition')
               ->withConsecutive(
                   [12, 23, 34],
                   [45, 56, 67]
               )
               ->willReturnOnConsecutiveCalls(
                   78,
                   89
               );
        
        /* @var PointInterface $result */
        $result = $this->invokeMethod($filter, 'calculateDrawPoint', $temporaryImage, $layerImage);
        $this->assertSame(78, $result->getX());
        $this->assertSame(89, $result->getY());
    }

    /**
     * Provides the data for the calculateDrawPosition test.
     * @return array<mixed>
     */
    public function provideCalculateDrawPosition(): array
    {
        return [
            [64, 20, 7, 29],
            [64, 20, -100, 0],
            [64, 20, 100, 44],
        ];
    }

    /**
     * Tests the calculateDrawPosition method.
     * @param int $temporarySize
     * @param int $layerSize
     * @param int $offset
     * @param int $expectedResult
     * @throws ReflectionException
     * @covers ::calculateDrawPosition
     * @dataProvider provideCalculateDrawPosition
     */
    public function testCalculateDrawPosition(
        int $temporarySize,
        int $layerSize,
        int $offset,
        int $expectedResult
    ): void {
        $filter = new ScaledLayerFilter(new Layer(), 1337);

        $result = $this->invokeMethod($filter, 'calculateDrawPosition', $temporarySize, $layerSize, $offset);
        $this->assertSame($expectedResult, $result);
    }
}
