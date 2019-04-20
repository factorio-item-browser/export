<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Command\Reduce;

use FactorioItemBrowser\Export\Command\Reduce\ReduceCombinationCommand;
use FactorioItemBrowser\Export\Command\Reduce\ReduceCombinationCommandFactory;
use FactorioItemBrowser\Export\ExportData\RawExportDataService;
use FactorioItemBrowser\Export\ExportData\ReducedExportDataService;
use FactorioItemBrowser\Export\Reducer\Combination\CombinationReducerManager;
use FactorioItemBrowser\ExportData\Registry\EntityRegistry;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the ReduceCombinationCommandFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Command\Reduce\ReduceCombinationCommandFactory
 */
class ReduceCombinationCommandFactoryTest extends TestCase
{
    /**
     * Tests the invoking.
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        /* @var RawExportDataService|MockObject $rawExportDataService */
        $rawExportDataService = $this->getMockBuilder(RawExportDataService::class)
                                     ->setMethods(['getCombinationRegistry'])
                                     ->disableOriginalConstructor()
                                     ->getMock();
        $rawExportDataService->expects($this->once())
                             ->method('getCombinationRegistry')
                             ->willReturn($this->createMock(EntityRegistry::class));

        /* @var ReducedExportDataService|MockObject $reducedExportDataService */
        $reducedExportDataService = $this->getMockBuilder(ReducedExportDataService::class)
                                         ->setMethods(['getCombinationRegistry'])
                                         ->disableOriginalConstructor()
                                         ->getMock();
        $reducedExportDataService->expects($this->once())
                                 ->method('getCombinationRegistry')
                                 ->willReturn($this->createMock(EntityRegistry::class));

        /* @var ContainerInterface|MockObject $container */
        $container = $this->getMockBuilder(ContainerInterface::class)
                          ->setMethods(['get'])
                          ->getMockForAbstractClass();
        $container->expects($this->exactly(3))
                  ->method('get')
                  ->withConsecutive(
                      [RawExportDataService::class],
                      [ReducedExportDataService::class],
                      [CombinationReducerManager::class]
                  )
                  ->willReturnOnConsecutiveCalls(
                      $rawExportDataService,
                      $reducedExportDataService,
                      $this->createMock(CombinationReducerManager::class)
                  );

        $factory = new ReduceCombinationCommandFactory();
        $factory($container, ReduceCombinationCommand::class);
    }
}
