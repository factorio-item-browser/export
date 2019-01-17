<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Renderer;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Mod\ModFileManager;
use FactorioItemBrowser\Export\Renderer\Filter\ScaledLayerFilter;
use FactorioItemBrowser\Export\Renderer\IconRenderer;
use FactorioItemBrowser\ExportData\Entity\Icon;
use FactorioItemBrowser\ExportData\Entity\Icon\Color;
use FactorioItemBrowser\ExportData\Entity\Icon\Layer;
use FactorioItemBrowser\ExportData\Entity\Mod;
use FactorioItemBrowser\ExportData\Registry\ModRegistry;
use Imagine\Filter\FilterInterface;
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
     * Tests the constructing.
     * @covers ::__construct
     * @throws ReflectionException
     */
    public function testConstruct(): void
    {
        /* @var ImagineInterface $imagine */
        $imagine = $this->createMock(ImagineInterface::class);
        /* @var ModFileManager $modFileManager */
        $modFileManager = $this->createMock(ModFileManager::class);
        /* @var ModRegistry $modRegistry */
        $modRegistry = $this->createMock(ModRegistry::class);

        $renderer = new IconRenderer($imagine, $modFileManager, $modRegistry);

        $this->assertSame($imagine, $this->extractProperty($renderer, 'imagine'));
        $this->assertSame($modFileManager, $this->extractProperty($renderer, 'modFileManager'));
        $this->assertSame($modRegistry, $this->extractProperty($renderer, 'modRegistry'));
    }

    /**
     * Tests the render method.
     * @throws ExportException
     * @covers ::render
     */
    public function testRender(): void
    {
        $iconSize = 42;
        $size = 21;
        $imageContent = 'abc';

        /* @var Layer $layer1 */
        $layer1 = $this->createMock(Layer::class);
        /* @var Layer $layer2 */
        $layer2 = $this->createMock(Layer::class);

        $icon = new Icon();
        $icon->setSize($iconSize)
             ->setLayers([$layer1, $layer2]);

        /* @var ImageInterface $image1 */
        $image1 = $this->createMock(ImageInterface::class);
        /* @var ImageInterface $image2 */
        $image2 = $this->createMock(ImageInterface::class);

        /* @var ImageInterface|MockObject $image3 */
        $image3 = $this->getMockBuilder(ImageInterface::class)
                       ->setMethods(['resize', 'get'])
                       ->getMockForAbstractClass();
        $image3->expects($this->once())
               ->method('resize')
               ->with($this->callback(function (BoxInterface $box) use ($size): bool {
                   $this->assertSame($size, $box->getWidth());
                   $this->assertSame($size, $box->getHeight());
                   return true;
               }))
               ->willReturnSelf();
        $image3->expects($this->once())
               ->method('get')
               ->with('png')
               ->willReturn($imageContent);

        /* @var IconRenderer|MockObject $renderer */
        $renderer = $this->getMockBuilder(IconRenderer::class)
                         ->setMethods(['createImage', 'renderLayer'])
                         ->disableOriginalConstructor()
                         ->getMock();
        $renderer->expects($this->once())
                 ->method('createImage')
                 ->with($iconSize)
                 ->willReturn($image1);
        $renderer->expects($this->exactly(2))
                 ->method('renderLayer')
                 ->withConsecutive(
                     [$image1, $layer1, $iconSize],
                     [$image2, $layer2, $iconSize]
                 )
                 ->willReturnOnConsecutiveCalls(
                     $image2,
                     $image3
                 );

        $result = $renderer->render($icon, $size);
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

        /* @var ImagineInterface|MockObject $imagine */
        $imagine = $this->getMockBuilder(ImagineInterface::class)
                        ->setMethods(['create'])
                        ->getMockForAbstractClass();
        $imagine->expects($this->once())
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

        /* @var ModFileManager $modFileManager */
        $modFileManager = $this->createMock(ModFileManager::class);
        /* @var ModRegistry $modRegistry */
        $modRegistry = $this->createMock(ModRegistry::class);

        $renderer = new IconRenderer($imagine, $modFileManager, $modRegistry);
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

        /* @var FilterInterface|MockObject $scaledLayerFilter */
        $scaledLayerFilter = $this->getMockBuilder(FilterInterface::class)
                                  ->setMethods(['apply'])
                                  ->getMockForAbstractClass();
        $scaledLayerFilter->expects($this->once())
                          ->method('apply')
                          ->with($layerImage)
                          ->willReturn($scaledImage);

        /* @var FilterInterface|MockObject $tintedLayerFilter */
        $tintedLayerFilter = $this->getMockBuilder(FilterInterface::class)
                                  ->setMethods(['apply'])
                                  ->getMockForAbstractClass();
        $tintedLayerFilter->expects($this->once())
                          ->method('apply')
                          ->with($image)
                          ->willReturn($tintedImage);

        /* @var IconRenderer|MockObject $renderer */
        $renderer = $this->getMockBuilder(IconRenderer::class)
                         ->setMethods(['createScaledLayerFilter', 'createLayerImage', 'createTintedLayerFilter'])
                         ->disableOriginalConstructor()
                         ->getMock();
        $renderer->expects($this->once())
                 ->method('createScaledLayerFilter')
                 ->with($layer, $size)
                 ->willReturn($scaledLayerFilter);
        $renderer->expects($this->once())
                 ->method('createLayerImage')
                 ->with($layer)
                 ->willReturn($layerImage);
        $renderer->expects($this->once())
                 ->method('createTintedLayerFilter')
                 ->with($layer, $scaledImage)
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

        /* @var ImagineInterface|MockObject $imagine */
        $imagine = $this->getMockBuilder(ImagineInterface::class)
                        ->setMethods(['load'])
                        ->getMockForAbstractClass();
        $imagine->expects($this->once())
                ->method('load')
                ->with($content)
                ->willReturn($image);

        /* @var ModRegistry $modRegistry */
        $modRegistry = $this->createMock(ModRegistry::class);
        /* @var ModFileManager $modFileManager */
        $modFileManager = $this->createMock(ModFileManager::class);

        /* @var IconRenderer|MockObject $renderer */
        $renderer = $this->getMockBuilder(IconRenderer::class)
                         ->setMethods(['loadLayerImage'])
                         ->setConstructorArgs([$imagine, $modFileManager, $modRegistry])
                         ->getMock();
        $renderer->expects($this->once())
                 ->method('loadLayerImage')
                 ->with($layerFileName)
                 ->willReturn($content);

        $result = $this->invokeMethod($renderer, 'createLayerImage', $layer);

        $this->assertSame($image, $result);
    }

    /**
     * Provides the data for the loadLayerImage test.
     * @return array
     */
    public function provideLoadLayerImage(): array
    {
        return [
            ['__abc__/def/ghi.png', null, 'abc', (new Mod())->setName('abc'), 'def/ghi.png'],
            ['__abc__/def/ghi.png', 'Mod not known: abc', 'abc', null, null],
            ['fail', 'Unable to understand image file name: fail', null, null, null],
        ];
    }

    /**
     * Tests the loadLayerImage method.
     * @param string $layerFileName
     * @param string|null $expectedExceptionMessage
     * @param string|null $expectedModName
     * @param Mod|null $resultGetMod
     * @param string|null $expectedFileName
     * @throws ReflectionException
     * @covers ::loadLayerImage
     * @dataProvider provideLoadLayerImage
     */
    public function testLoadLayerImage(
        string $layerFileName,
        ?string $expectedExceptionMessage,
        ?string $expectedModName,
        ?Mod $resultGetMod,
        ?string $expectedFileName
    ): void {
        $layerImageContent = 'foo';

        /* @var ModRegistry|MockObject $modRegistry */
        $modRegistry = $this->getMockBuilder(ModRegistry::class)
                            ->setMethods(['get'])
                            ->disableOriginalConstructor()
                            ->getMock();
        $modRegistry->expects($expectedModName === null ? $this->never() : $this->once())
                    ->method('get')
                    ->with($expectedModName)
                    ->willReturn($resultGetMod);

        /* @var ModFileManager|MockObject $modFileManager */
        $modFileManager = $this->getMockBuilder(ModFileManager::class)
                               ->setMethods(['getFile'])
                               ->disableOriginalConstructor()
                               ->getMock();
        $modFileManager->expects($expectedFileName === null ? $this->never() : $this->once())
                       ->method('getFile')
                       ->with($resultGetMod, $expectedFileName)
                       ->willReturn($layerImageContent);

        if ($expectedExceptionMessage !== null) {
            $this->expectException(ExportException::class);
            $this->expectExceptionMessage($expectedExceptionMessage);
        }

        /* @var ImagineInterface $imagine */
        $imagine = $this->createMock(ImagineInterface::class);

        $renderer = new IconRenderer($imagine, $modFileManager, $modRegistry);

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

        /* @var ImagineInterface $imagine */
        $imagine = $this->createMock(ImagineInterface::class);
        /* @var ModFileManager $modFileManager */
        $modFileManager = $this->createMock(ModFileManager::class);
        /* @var ModRegistry $modRegistry */
        $modRegistry = $this->createMock(ModRegistry::class);

        $renderer = new IconRenderer($imagine, $modFileManager, $modRegistry);
        /* @var ScaledLayerFilter $result*/
        $result = $this->invokeMethod($renderer, 'createScaledLayerFilter', $layer, $size);

        $this->assertSame($layer, $this->extractProperty($result, 'layer'));
        $this->assertSame($size, $this->extractProperty($result, 'size'));
        $this->assertSame($imagine, $result->getImagine());
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

        /* @var Layer|MockObject $layer */
        $layer = $this->getMockBuilder(Layer::class)
                      ->setMethods(['getTintColor'])
                      ->disableOriginalConstructor()
                      ->getMock();
        $layer->expects($this->once())
              ->method('getTintColor')
              ->willReturn($color);

        /* @var IconRenderer|MockObject $renderer */
        $renderer = $this->getMockBuilder(IconRenderer::class)
                         ->setMethods(['convertColor'])
                         ->disableOriginalConstructor()
                         ->getMock();
        $renderer->expects($this->once())
                 ->method('convertColor')
                 ->with($color)
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

        /* @var ImagineInterface $imagine */
        $imagine = $this->createMock(ImagineInterface::class);
        /* @var ModFileManager $modFileManager */
        $modFileManager = $this->createMock(ModFileManager::class);
        /* @var ModRegistry $modRegistry */
        $modRegistry = $this->createMock(ModRegistry::class);

        $renderer = new IconRenderer($imagine, $modFileManager, $modRegistry);
        /* @var ColorInterface $result*/
        $result = $this->invokeMethod($renderer, 'convertColor', $color);

        $this->assertSame(64, $result->getValue(ColorInterface::COLOR_RED));
        $this->assertSame(128, $result->getValue(ColorInterface::COLOR_GREEN));
        $this->assertSame(191, $result->getValue(ColorInterface::COLOR_BLUE));
        $this->assertSame(42, $result->getAlpha());
    }
}
