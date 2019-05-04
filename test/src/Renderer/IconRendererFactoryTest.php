<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Renderer;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Export\ExportData\RawExportDataService;
use FactorioItemBrowser\Export\Mod\ModFileManager;
use FactorioItemBrowser\Export\Renderer\IconRenderer;
use FactorioItemBrowser\Export\Renderer\IconRendererFactory;
use FactorioItemBrowser\ExportData\Registry\ModRegistry;
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
     * @throws ReflectionException
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        /* @var ImagineInterface&MockObject $imagine */
        $imagine = $this->createMock(ImagineInterface::class);
        /* @var ModFileManager&MockObject $modFileManager */
        $modFileManager = $this->createMock(ModFileManager::class);
        /* @var ModRegistry&MockObject $modRegistry */
        $modRegistry = $this->createMock(ModRegistry::class);

        $expectedResult = new IconRenderer($imagine, $modFileManager, $modRegistry);

        /* @var RawExportDataService&MockObject $rawExportDataService */
        $rawExportDataService = $this->createMock(RawExportDataService::class);
        $rawExportDataService->expects($this->once())
                             ->method('getModRegistry')
                             ->willReturn($modRegistry);

        /* @var ContainerInterface&MockObject $container */
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->exactly(3))
                  ->method('get')
                  ->withConsecutive(
                      [ImagineInterface::class],
                      [ModFileManager::class],
                      [RawExportDataService::class]
                  )
                  ->willReturnOnConsecutiveCalls(
                      $imagine,
                      $modFileManager,
                      $rawExportDataService
                  );

        $factory = new IconRendererFactory();
        $result = $factory($container, IconRenderer::class);

        $this->assertEquals($expectedResult, $result);
    }
}
