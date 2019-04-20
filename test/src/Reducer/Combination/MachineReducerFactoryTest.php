<?php

namespace FactorioItemBrowserTest\Export\Reducer\Combination;

use FactorioItemBrowser\Export\ExportData\RawExportDataService;
use FactorioItemBrowser\Export\ExportData\ReducedExportDataService;
use FactorioItemBrowser\Export\Reducer\Combination\MachineReducer;
use FactorioItemBrowser\Export\Reducer\Combination\MachineReducerFactory;
use FactorioItemBrowser\ExportData\Registry\EntityRegistry;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the MachineReducerFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Reducer\Combination\MachineReducerFactory
 */
class MachineReducerFactoryTest extends TestCase
{
    /**
     * Tests the invoking.
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        /* @var EntityRegistry $rawMachineRegistry */
        $rawMachineRegistry = $this->createMock(EntityRegistry::class);
        /* @var EntityRegistry $reducedMachineRegistry */
        $reducedMachineRegistry = $this->createMock(EntityRegistry::class);

        /* @var RawExportDataService|MockObject $rawExportDataService */
        $rawExportDataService = $this->getMockBuilder(RawExportDataService::class)
                                     ->setMethods(['getMachineRegistry'])
                                     ->disableOriginalConstructor()
                                     ->getMock();
        $rawExportDataService->expects($this->once())
                             ->method('getMachineRegistry')
                             ->willReturn($rawMachineRegistry);

        /* @var ReducedExportDataService|MockObject $reducedExportDataService */
        $reducedExportDataService = $this->getMockBuilder(ReducedExportDataService::class)
                                         ->setMethods(['getMachineRegistry'])
                                         ->disableOriginalConstructor()
                                         ->getMock();
        $reducedExportDataService->expects($this->once())
                                 ->method('getMachineRegistry')
                                 ->willReturn($reducedMachineRegistry);

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

        $factory = new MachineReducerFactory();
        $factory($container, MachineReducer::class);
    }
}
