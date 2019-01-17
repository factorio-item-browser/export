<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Renderer;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Export\ExportData\RawExportDataService;
use FactorioItemBrowser\Export\Mod\ModFileManager;
use FactorioItemBrowser\Export\Renderer\IconRenderer;
use FactorioItemBrowser\Export\Renderer\IconRendererFactory;
use FactorioItemBrowser\ExportData\Registry\ModRegistry;
use Imagine\Gd\Imagine;
use Imagine\Image\ImagineInterface;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the IconRendererFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Renderer\IconRendererFactory
 */
class IconRendererFactoryTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests the invoking.
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        /* @var ModRegistry $modRegistry */
        $modRegistry = $this->createMock(ModRegistry::class);

        /* @var RawExportDataService|MockObject $rawExportDataService */
        $rawExportDataService = $this->getMockBuilder(RawExportDataService::class)
                                     ->setMethods(['getModRegistry'])
                                     ->disableOriginalConstructor()
                                     ->getMock();
        $rawExportDataService->expects($this->once())
                             ->method('getModRegistry')
                             ->willReturn($modRegistry);


        /* @var ContainerInterface|MockObject $container */
        $container = $this->getMockBuilder(ContainerInterface::class)
                          ->setMethods(['get'])
                          ->getMockForAbstractClass();
        $container->expects($this->exactly(2))
                  ->method('get')
                  ->withConsecutive(
                      [RawExportDataService::class],
                      [ModFileManager::class]
                  )
                  ->willReturnOnConsecutiveCalls(
                      $rawExportDataService,
                      $this->createMock(ModFileManager::class)
                  );

        /* @var IconRendererFactory|MockObject $factory */
        $factory = $this->getMockBuilder(IconRendererFactory::class)
                        ->setMethods(['createImagine'])
                        ->getMock();
        $factory->expects($this->once())
                ->method('createImagine')
                ->willReturn($this->createMock(ImagineInterface::class));

        $factory($container, IconRenderer::class);
    }

    /**
     * Tests the createImagine method.
     * @throws ReflectionException
     * @covers ::createImagine
     */
    public function testCreateImagine(): void
    {
        $factory = new IconRendererFactory();
        $result = $this->invokeMethod($factory, 'createImagine');
        $this->assertInstanceOf(Imagine::class, $result);
    }
}
