<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Command\Reduce;

use FactorioItemBrowser\Export\Command\Reduce\ReduceModCommand;
use FactorioItemBrowser\Export\Command\Reduce\ReduceModCommandFactory;
use FactorioItemBrowser\Export\ExportData\RawExportDataService;
use FactorioItemBrowser\Export\ExportData\ReducedExportDataService;
use FactorioItemBrowser\ExportData\Registry\EntityRegistry;
use FactorioItemBrowser\ExportData\Registry\ModRegistry;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the ReduceModCommandFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Command\Reduce\ReduceModCommandFactory
 */
class ReduceModCommandFactoryTest extends TestCase
{
    /**
     * Tests the invoking.
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        /* @var RawExportDataService|MockObject $rawExportDataService */
        $rawExportDataService = $this->getMockBuilder(RawExportDataService::class)
                                     ->setMethods(['getModRegistry'])
                                     ->disableOriginalConstructor()
                                     ->getMock();
        $rawExportDataService->expects($this->once())
                             ->method('getModRegistry')
                             ->willReturn($this->createMock(ModRegistry::class));

        /* @var ReducedExportDataService|MockObject $reducedExportDataService */
        $reducedExportDataService = $this->getMockBuilder(ReducedExportDataService::class)
                                         ->setMethods(['getModRegistry', 'getCombinationRegistry'])
                                         ->disableOriginalConstructor()
                                         ->getMock();
        $reducedExportDataService->expects($this->once())
                                 ->method('getModRegistry')
                                 ->willReturn($this->createMock(ModRegistry::class));
        $reducedExportDataService->expects($this->once())
                                 ->method('getCombinationRegistry')
                                 ->willReturn($this->createMock(EntityRegistry::class));

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

        $factory = new ReduceModCommandFactory();
        $factory($container, ReduceModCommand::class);
    }
}
