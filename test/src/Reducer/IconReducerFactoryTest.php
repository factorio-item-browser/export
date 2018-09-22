<?php

namespace FactorioItemBrowserTest\Export\Reducer;

use FactorioItemBrowser\Export\ExportData\RawExportDataService;
use FactorioItemBrowser\Export\ExportData\ReducedExportDataService;
use FactorioItemBrowser\Export\Reducer\IconReducer;
use FactorioItemBrowser\Export\Reducer\IconReducerFactory;
use FactorioItemBrowser\ExportData\Registry\EntityRegistry;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the IconReducerFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Reducer\IconReducerFactory
 */
class IconReducerFactoryTest extends TestCase
{
    /**
     * Tests the invoking.
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        /* @var EntityRegistry $rawIconRegistry */
        $rawIconRegistry = $this->createMock(EntityRegistry::class);
        /* @var EntityRegistry $reducedIconRegistry */
        $reducedIconRegistry = $this->createMock(EntityRegistry::class);

        /* @var RawExportDataService|MockObject $rawExportDataService */
        $rawExportDataService = $this->getMockBuilder(RawExportDataService::class)
                                     ->setMethods(['getIconRegistry'])
                                     ->disableOriginalConstructor()
                                     ->getMock();
        $rawExportDataService->expects($this->once())
                             ->method('getIconRegistry')
                             ->willReturn($rawIconRegistry);

        /* @var ReducedExportDataService|MockObject $reducedExportDataService */
        $reducedExportDataService = $this->getMockBuilder(ReducedExportDataService::class)
                                         ->setMethods(['getIconRegistry'])
                                         ->disableOriginalConstructor()
                                         ->getMock();
        $reducedExportDataService->expects($this->once())
                                 ->method('getIconRegistry')
                                 ->willReturn($reducedIconRegistry);

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

        $factory = new IconReducerFactory();
        $factory($container, IconReducer::class);
    }
}
