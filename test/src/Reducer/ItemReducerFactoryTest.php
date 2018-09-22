<?php

namespace FactorioItemBrowserTest\Export\Reducer;

use FactorioItemBrowser\Export\ExportData\RawExportDataService;
use FactorioItemBrowser\Export\ExportData\ReducedExportDataService;
use FactorioItemBrowser\Export\Reducer\ItemReducer;
use FactorioItemBrowser\Export\Reducer\ItemReducerFactory;
use FactorioItemBrowser\ExportData\Registry\EntityRegistry;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the ItemReducerFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Reducer\ItemReducerFactory
 */
class ItemReducerFactoryTest extends TestCase
{
    /**
     * Tests the invoking.
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        /* @var EntityRegistry $rawItemRegistry */
        $rawItemRegistry = $this->createMock(EntityRegistry::class);
        /* @var EntityRegistry $reducedItemRegistry */
        $reducedItemRegistry = $this->createMock(EntityRegistry::class);

        /* @var RawExportDataService|MockObject $rawExportDataService */
        $rawExportDataService = $this->getMockBuilder(RawExportDataService::class)
                                     ->setMethods(['getItemRegistry'])
                                     ->disableOriginalConstructor()
                                     ->getMock();
        $rawExportDataService->expects($this->once())
                             ->method('getItemRegistry')
                             ->willReturn($rawItemRegistry);

        /* @var ReducedExportDataService|MockObject $reducedExportDataService */
        $reducedExportDataService = $this->getMockBuilder(ReducedExportDataService::class)
                                         ->setMethods(['getItemRegistry'])
                                         ->disableOriginalConstructor()
                                         ->getMock();
        $reducedExportDataService->expects($this->once())
                                 ->method('getItemRegistry')
                                 ->willReturn($reducedItemRegistry);

        /* @var ContainerInterface|MockObject $container */
        $container = $this->getMockBuilder(ContainerInterface::class)
                          ->setMethods(['get'])
                          ->getMockForAbstractClass();
        $container->expects($this->exactly(2))
                  ->method('get')
                  ->withConsecutive(
                      [RawExportDataService::class],
                      [ReducedExportDataService::class]
                  )
                  ->willReturnOnConsecutiveCalls(
                      $rawExportDataService,
                      $reducedExportDataService
                  );

        $factory = new ItemReducerFactory();
        $factory($container, ItemReducer::class);
    }
}
