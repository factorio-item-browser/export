<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Renderer;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Mod\ModFileManager;
use FactorioItemBrowser\Export\Renderer\Filter\ScaledLayerFilter;
use FactorioItemBrowser\Export\Renderer\IconRenderer;
use FactorioItemBrowser\ExportData\Entity\Icon;
use FactorioItemBrowser\ExportData\Entity\Icon\Color;
use FactorioItemBrowser\ExportData\Entity\Icon\Layer;
use Imagine\Filter\FilterInterface;
use Imagine\Image\Box;
use Imagine\Image\BoxInterface;
use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;
use Imagine\Image\Palette\Color\ColorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the IconRenderer class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Renderer\IconRenderer
 */
class IconRendererTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked imagine.
     * @var ImagineInterface&MockObject
     */
    protected $imagine;

    /**
     * The mocked mod file manager.
     * @var ModFileManager&MockObject
     */
    protected $modFileManager;

    /**
     * Sets up the test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->imagine = $this->createMock(ImagineInterface::class);
        $this->modFileManager = $this->createMock(ModFileManager::class);
    }

    /**
     * Tests the constructing.
     * @covers ::__construct
     * @throws ReflectionException
     */
    public function testConstruct(): void
    {
        $renderer = new IconRenderer($this->imagine, $this->modFileManager);

        $this->assertSame($this->imagine, $this->extractProperty($renderer, 'imagine'));
        $this->assertSame($this->modFileManager, $this->extractProperty($renderer, 'modFileManager'));
    }

    /**
     * Tests the render method.
     * @throws ExportException
     * @covers ::render
     */
    public function testRender(): void
    {
        $iconSize = 42;
        $renderedSize = 1337;
        $imageContent = 'abc';

        /* @var Layer&MockObject $layer1 */
        $layer1 = $this->createMock(Layer::class);
        /* @var Layer&MockObject $layer2 */
        $layer2 = $this->createMock(Layer::class);
        /* @var ImageInterface&MockObject $image1 */
        $image1 = $this->createMock(ImageInterface::class);
        /* @var ImageInterface&MockObject $image2 */
        $image2 = $this->createMock(ImageInterface::class);

        /* @var ImageInterface&MockObject $image3 */
        $image3 = $this->createMock(ImageInterface::class);
        $image3->expects($this->once())
               ->method('get')
               ->with($this->identicalTo('png'))
               ->willReturn($imageContent);

        /* @var Icon&MockObject $icon */
        $icon = $this->createMock(Icon::class);
        $icon->expects($this->atLeastOnce())
             ->method('getSize')
             ->willReturn($iconSize);
        $icon->expects($this->once())
             ->method('getLayers')
             ->willReturn([$layer1, $layer2]);
        $icon->expects($this->once())
             ->method('getRenderedSize')
             ->willReturn($renderedSize);

        /* @var IconRenderer&MockObject $renderer */
        $renderer = $this->getMockBuilder(IconRenderer::class)
                         ->onlyMethods(['createImage', 'renderLayer', 'resizeImage'])
                         ->setConstructorArgs([$this->imagine, $this->modFileManager])
                         ->getMock();
        $renderer->expects($this->once())
                 ->method('createImage')
                 ->with($this->identicalTo($iconSize))
                 ->willReturn($image1);
        $renderer->expects($this->exactly(2))
                 ->method('renderLayer')
                 ->withConsecutive(
                     [$this->identicalTo($image1), $this->identicalTo($layer1), $this->identicalTo($iconSize)],
                     [$this->identicalTo($image2), $this->identicalTo($layer2), $this->identicalTo($iconSize)]
                 )
                 ->willReturnOnConsecutiveCalls(
                     $image2,
                     $image3
                 );
        $renderer->expects($this->once())
                 ->method('resizeImage')
                 ->with($this->identicalTo($image3), $this->identicalTo($renderedSize));

        $result = $renderer->render($icon);
        $this->assertSame($imageContent, $result);
    }

    /**
     * Tests the createImage method.
     * @throws ReflectionException
     * @covers ::createImage
     */
    public function testCreateImage(): void
    {
        $size = 32;

        /* @var ImageInterface $image */
        $image = $this->createMock(ImageInterface::class);

        $this->imagine->expects($this->once())
                      ->method('create')
                      ->with(
                          $this->callback(function (BoxInterface $box) use ($size): bool {
                              $this->assertSame($size, $box->getWidth());
                              $this->assertSame($size, $box->getHeight());
                              return true;
                          }),
                          $this->callback(function (ColorInterface $color): bool {
                              $this->assertSame(255, $color->getValue(ColorInterface::COLOR_RED));
                              $this->assertSame(255, $color->getValue(ColorInterface::COLOR_GREEN));
                              $this->assertSame(255, $color->getValue(ColorInterface::COLOR_BLUE));
                              $this->assertSame(0, $color->getAlpha());
                              return true;
                          })
                      )
                      ->willReturn($image);

        $renderer = new IconRenderer($this->imagine, $this->modFileManager);
        $result = $this->invokeMethod($renderer, 'createImage', $size);

        $this->assertSame($image, $result);
    }

    /**
     * Tests the renderLayer method.
     * @throws ReflectionException
     * @covers ::renderLayer
     */
    public function testRenderLayer(): void
    {
        $layer = (new Layer())->setFileName('abc');
        $size = 42;

        /* @var ImageInterface $layerImage */
        $layerImage = $this->createMock(ImageInterface::class);
        /* @var ImageInterface $scaledImage */
        $scaledImage = $this->createMock(ImageInterface::class);
        /* @var ImageInterface $image */
        $image = $this->createMock(ImageInterface::class);
        /* @var ImageInterface $tintedImage */
        $tintedImage = $this->createMock(ImageInterface::class);

        /* @var FilterInterface&MockObject $scaledLayerFilter */
        $scaledLayerFilter = $this->createMock(FilterInterface::class);
        $scaledLayerFilter->expects($this->once())
                          ->method('apply')
                          ->with($this->identicalTo($layerImage))
                          ->willReturn($scaledImage);

        /* @var FilterInterface&MockObject $tintedLayerFilter */
        $tintedLayerFilter = $this->createMock(FilterInterface::class);
        $tintedLayerFilter->expects($this->once())
                          ->method('apply')
                          ->with($this->identicalTo($image))
                          ->willReturn($tintedImage);

        /* @var IconRenderer&MockObject $renderer */
        $renderer = $this->getMockBuilder(IconRenderer::class)
                         ->onlyMethods(['createScaledLayerFilter', 'createLayerImage', 'createTintedLayerFilter'])
                         ->setConstructorArgs([$this->imagine, $this->modFileManager])
                         ->getMock();
        $renderer->expects($this->once())
                 ->method('createScaledLayerFilter')
                 ->with($this->identicalTo($layer), $this->identicalTo($size))
                 ->willReturn($scaledLayerFilter);
        $renderer->expects($this->once())
                 ->method('createLayerImage')
                 ->with($this->identicalTo($layer))
                 ->willReturn($layerImage);
        $renderer->expects($this->once())
                 ->method('createTintedLayerFilter')
                 ->with($this->identicalTo($layer), $this->identicalTo($scaledImage))
                 ->willReturn($tintedLayerFilter);

        $result = $this->invokeMethod($renderer, 'renderLayer', $image, $layer, $size);
        $this->assertSame($tintedImage, $result);
    }

    /**
     * Tests the createLayerImage method.
     * @throws ReflectionException
     * @covers ::createLayerImage
     */
    public function testCreateLayerImage(): void
    {
        $layerFileName = 'abc';
        $layer = (new Layer())->setFileName($layerFileName);
        $content = 'def';
        /* @var ImageInterface $image */
        $image = $this->createMock(ImageInterface::class);

        $this->imagine->expects($this->once())
                      ->method('load')
                      ->with($this->identicalTo($content))
                      ->willReturn($image);

        /* @var IconRenderer&MockObject $renderer */
        $renderer = $this->getMockBuilder(IconRenderer::class)
                         ->onlyMethods(['loadLayerImage'])
                         ->setConstructorArgs([$this->imagine, $this->modFileManager])
                         ->getMock();
        $renderer->expects($this->once())
                 ->method('loadLayerImage')
                 ->with($this->identicalTo($layerFileName))
                 ->willReturn($content);

        $result = $this->invokeMethod($renderer, 'createLayerImage', $layer);

        $this->assertSame($image, $result);
    }

    /**
     * Provides the data for the loadLayerImage test.
     * @return array<mixed>
     */
    public function provideLoadLayerImage(): array
    {
        return [
            ['__abc__/def/ghi.png', null, 'abc', 'def/ghi.png'],
            ['fail', 'Unable to understand image file name: fail', null, null],
        ];
    }

    /**
     * Tests the loadLayerImage method.
     * @param string $layerFileName
     * @param string|null $expectedExceptionMessage
     * @param string|null $expectedModName
     * @param string|null $expectedFileName
     * @throws ReflectionException
     * @covers ::loadLayerImage
     * @dataProvider provideLoadLayerImage
     */
    public function testLoadLayerImage(
        string $layerFileName,
        ?string $expectedExceptionMessage,
        ?string $expectedModName,
        ?string $expectedFileName
    ): void {
        $layerImageContent = 'foo';

        $this->modFileManager->expects($expectedFileName === null ? $this->never() : $this->once())
                             ->method('readFile')
                             ->with($this->identicalTo($expectedModName), $this->identicalTo($expectedFileName))
                             ->willReturn($layerImageContent);

        if ($expectedExceptionMessage !== null) {
            $this->expectException(ExportException::class);
            $this->expectExceptionMessage($expectedExceptionMessage);
        }

        $renderer = new IconRenderer($this->imagine, $this->modFileManager);

        $result = $this->invokeMethod($renderer, 'loadLayerImage', $layerFileName);
        $this->assertSame($layerImageContent, $result);
    }

    /**
     * Tests the createScaledLayerFilter method.
     * @throws ReflectionException
     * @covers ::createScaledLayerFilter
     */
    public function testCreateScaledLayerFilter(): void
    {
        /* @var Layer $layer */
        $layer = $this->createMock(Layer::class);
        $size = 42;

        $renderer = new IconRenderer($this->imagine, $this->modFileManager);
        /* @var ScaledLayerFilter $result*/
        $result = $this->invokeMethod($renderer, 'createScaledLayerFilter', $layer, $size);

        $this->assertSame($layer, $this->extractProperty($result, 'layer'));
        $this->assertSame($size, $this->extractProperty($result, 'size'));
        $this->assertSame($this->imagine, $result->getImagine());
    }

    /**
     * Tests the createTintedLayerFilter method.
     * @throws ReflectionException
     * @covers ::createTintedLayerFilter
     */
    public function testCreateTintedLayerFilter(): void
    {
        /* @var Color $color */
        $color = $this->createMock(Color::class);
        /* @var ColorInterface $convertedColor */
        $convertedColor = $this->createMock(ColorInterface::class);
        /* @var ImageInterface $layerImage */
        $layerImage = $this->createMock(ImageInterface::class);

        /* @var Layer&MockObject $layer */
        $layer = $this->createMock(Layer::class);
        $layer->expects($this->once())
              ->method('getTint')
              ->willReturn($color);

        /* @var IconRenderer&MockObject $renderer */
        $renderer = $this->getMockBuilder(IconRenderer::class)
                         ->onlyMethods(['convertColor'])
                         ->setConstructorArgs([$this->imagine, $this->modFileManager])
                         ->getMock();
        $renderer->expects($this->once())
                 ->method('convertColor')
                 ->with($this->identicalTo($color))
                 ->willReturn($convertedColor);

        $result = $this->invokeMethod($renderer, 'createTintedLayerFilter', $layer, $layerImage);

        $this->assertSame($layerImage, $this->extractProperty($result, 'layerImage'));
        $this->assertSame($convertedColor, $this->extractProperty($result, 'tintColor'));
    }

    /**
     * Tests the convertColor method.
     * @throws ReflectionException
     * @covers ::convertColor
     */
    public function testConvertColor(): void
    {
        $color = new Color();
        $color->setRed(0.25)
              ->setGreen(0.5)
              ->setBlue(0.75)
              ->setAlpha(0.42);

        $renderer = new IconRenderer($this->imagine, $this->modFileManager);
        /* @var ColorInterface $result*/
        $result = $this->invokeMethod($renderer, 'convertColor', $color);

        $this->assertSame(64, $result->getValue(ColorInterface::COLOR_RED));
        $this->assertSame(128, $result->getValue(ColorInterface::COLOR_GREEN));
        $this->assertSame(191, $result->getValue(ColorInterface::COLOR_BLUE));
        $this->assertSame(42, $result->getAlpha());
    }

    /**
     * Provides the data for the resizeImage test.
     * @return array<mixed>
     */
    public function provideResizeImage(): array
    {
        return [
            [64, 64, 32],
            [64, 32, 64],
        ];
    }

    /**
     * Tests the resizeImage method.
     * @param int $imageSize
     * @param int $width
     * @param int $height
     * @throws ReflectionException
     * @covers ::resizeImage
     * @dataProvider provideResizeImage
     */
    public function testResizeImage(int $imageSize, int $width, int $height): void
    {
        $expectedBox = new Box($imageSize, $imageSize);

        /* @var BoxInterface&MockObject $size */
        $size = $this->createMock(BoxInterface::class);
        $size->expects($this->any())
             ->method('getWidth')
             ->willReturn($width);
        $size->expects($this->any())
             ->method('getHeight')
             ->willReturn($height);

        /* @var ImageInterface&MockObject $image */
        $image = $this->createMock(ImageInterface::class);
        $image->expects($this->any())
              ->method('getSize')
              ->willReturn($size);
        $image->expects($this->once())
              ->method('resize')
              ->with($this->equalTo($expectedBox));

        $renderer = new IconRenderer($this->imagine, $this->modFileManager);
        $this->invokeMethod($renderer, 'resizeImage', $image, $imageSize);
    }

    /**
     * Tests the resizeImage method without actually resizing it.
     * @throws ReflectionException
     * @covers ::resizeImage
     */
    public function testResizeImageWithoutResizing(): void
    {
        $imageSize = 42;
        $width = 42;
        $height = 42;

        /* @var BoxInterface&MockObject $size */
        $size = $this->createMock(BoxInterface::class);
        $size->expects($this->any())
             ->method('getWidth')
             ->willReturn($width);
        $size->expects($this->any())
             ->method('getHeight')
             ->willReturn($height);

        /* @var ImageInterface&MockObject $image */
        $image = $this->createMock(ImageInterface::class);
        $image->expects($this->any())
              ->method('getSize')
              ->willReturn($size);
        $image->expects($this->never())
              ->method('resize');

        $renderer = new IconRenderer($this->imagine, $this->modFileManager);
        $this->invokeMethod($renderer, 'resizeImage', $image, $imageSize);
    }
}
