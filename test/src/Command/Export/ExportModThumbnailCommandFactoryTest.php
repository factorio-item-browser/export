<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Command\Export;

use FactorioItemBrowser\Export\Command\Export\ExportModThumbnailCommand;
use FactorioItemBrowser\Export\Command\Export\ExportModThumbnailCommandFactory;
use FactorioItemBrowser\Export\ExportData\RawExportDataService;
use FactorioItemBrowser\Export\Mod\ModFileManager;
use FactorioItemBrowser\ExportData\Registry\EntityRegistry;
use FactorioItemBrowser\ExportData\Registry\ModRegistry;
use Imagine\Image\ImagineInterface;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the ExportModThumbnailCommandFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Command\Export\ExportModThumbnailCommandFactory
 */
class ExportModThumbnailCommandFactoryTest extends TestCase
{
    /**
     * Tests the invoking.
     * @throws ReflectionException
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        /* @var EntityRegistry&MockObject $iconRegistry */
        $iconRegistry = $this->createMock(EntityRegistry::class);
        /* @var ImagineInterface&MockObject $imagine */
        $imagine = $this->createMock(ImagineInterface::class);
        /* @var ModFileManager&MockObject $modFileManager */
        $modFileManager = $this->createMock(ModFileManager::class);
        /* @var ModRegistry&MockObject $modRegistry */
        $modRegistry = $this->createMock(ModRegistry::class);

        $expectedResult = new ExportModThumbnailCommand($iconRegistry, $imagine, $modFileManager, $modRegistry);

        /* @var RawExportDataService&MockObject $rawExportDataService */
        $rawExportDataService = $this->createMock(RawExportDataService::class);
        $rawExportDataService->expects($this->once())
                             ->method('getIconRegistry')
                             ->willReturn($iconRegistry);
        $rawExportDataService->expects($this->once())
                             ->method('getModRegistry')
                             ->willReturn($modRegistry);

        /* @var ContainerInterface&MockObject $container */
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->exactly(3))
                  ->method('get')
                  ->withConsecutive(
                      [$this->identicalTo(ImagineInterface::class)],
                      [$this->identicalTo(ModFileManager::class)],
                      [$this->identicalTo(RawExportDataService::class)]
                  )
                  ->willReturnOnConsecutiveCalls(
                      $imagine,
                      $modFileManager,
                      $rawExportDataService
                  );

        $factory = new ExportModThumbnailCommandFactory();
        $result = $factory($container, ExportModThumbnailCommand::class);

        $this->assertEquals($expectedResult, $result);
    }
}
