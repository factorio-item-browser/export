<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Reducer\Mod;

use FactorioItemBrowser\Export\ExportData\RawExportDataService;
use FactorioItemBrowser\Export\ExportData\ReducedExportDataService;
use FactorioItemBrowser\Export\Reducer\Mod\ThumbnailReducer;
use FactorioItemBrowser\Export\Reducer\Mod\ThumbnailReducerFactory;
use FactorioItemBrowser\ExportData\Registry\EntityRegistry;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the ThumbnailReducerFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Reducer\Mod\ThumbnailReducerFactory
 */
class ThumbnailReducerFactoryTest extends TestCase
{
    /**
     * Tests the invoking.
     * @throws ReflectionException
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        /* @var EntityRegistry&MockObject $rawIconRegistry */
        $rawIconRegistry = $this->createMock(EntityRegistry::class);
        /* @var EntityRegistry&MockObject $reducedIconRegistry */
        $reducedIconRegistry = $this->createMock(EntityRegistry::class);

        $expectedResult = new ThumbnailReducer($rawIconRegistry, $reducedIconRegistry);

        /* @var RawExportDataService&MockObject $rawExportDataService */
        $rawExportDataService = $this->createMock(RawExportDataService::class);
        $rawExportDataService->expects($this->once())
                             ->method('getIconRegistry')
                             ->willReturn($rawIconRegistry);

        /* @var ReducedExportDataService&MockObject $reducedExportDataService */
        $reducedExportDataService = $this->createMock(ReducedExportDataService::class);
        $reducedExportDataService->expects($this->once())
                                 ->method('getIconRegistry')
                                 ->willReturn($reducedIconRegistry);

        /* @var ContainerInterface&MockObject $container */
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->exactly(2))
                  ->method('get')
                  ->withConsecutive(
                      [$this->identicalTo(RawExportDataService::class)],
                      [$this->identicalTo(ReducedExportDataService::class)]
                  )
                  ->willReturnOnConsecutiveCalls(
                      $rawExportDataService,
                      $reducedExportDataService
                  );

        $factory = new ThumbnailReducerFactory();
        $result = $factory($container, ThumbnailReducer::class);

        $this->assertEquals($expectedResult, $result);
    }
}
