<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Command\Reduce;

use FactorioItemBrowser\Export\Command\Reduce\ReduceModCommand;
use FactorioItemBrowser\Export\Command\Reduce\ReduceModCommandFactory;
use FactorioItemBrowser\Export\ExportData\RawExportDataService;
use FactorioItemBrowser\Export\ExportData\ReducedExportDataService;
use FactorioItemBrowser\Export\Reducer\Mod\ModReducerManager;
use FactorioItemBrowser\ExportData\Registry\ModRegistry;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

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
     * @throws ReflectionException
     */
    public function testInvoke(): void
    {
        /* @var ModReducerManager&MockObject $modReducerManager */
        $modReducerManager = $this->createMock(ModReducerManager::class);
        /* @var ModRegistry&MockObject $rawModRegistry */
        $rawModRegistry = $this->createMock(ModRegistry::class);
        /* @var ModRegistry&MockObject $reducedModRegistry */
        $reducedModRegistry = $this->createMock(ModRegistry::class);

        $expectedResult = new ReduceModCommand($modReducerManager, $rawModRegistry, $reducedModRegistry);

        /* @var RawExportDataService&MockObject $rawExportDataService */
        $rawExportDataService = $this->createMock(RawExportDataService::class);
        $rawExportDataService->expects($this->once())
                             ->method('getModRegistry')
                             ->willReturn($rawModRegistry);

        /* @var ReducedExportDataService&MockObject $reducedExportDataService */
        $reducedExportDataService = $this->createMock(ReducedExportDataService::class);
        $reducedExportDataService->expects($this->once())
                                 ->method('getModRegistry')
                                 ->willReturn($reducedModRegistry);

        /* @var ContainerInterface&MockObject $container */
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->exactly(3))
                  ->method('get')
                  ->withConsecutive(
                      [ModReducerManager::class],
                      [RawExportDataService::class],
                      [ReducedExportDataService::class]
                  )
                  ->willReturnOnConsecutiveCalls(
                      $modReducerManager,
                      $rawExportDataService,
                      $reducedExportDataService
                  );

        $factory = new ReduceModCommandFactory();
        $result = $factory($container, ReduceModCommand::class);

        $this->assertEquals($expectedResult, $result);
    }
}
