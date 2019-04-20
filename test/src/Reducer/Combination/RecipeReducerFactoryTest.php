<?php

namespace FactorioItemBrowserTest\Export\Reducer\Combination;

use FactorioItemBrowser\Export\ExportData\RawExportDataService;
use FactorioItemBrowser\Export\ExportData\ReducedExportDataService;
use FactorioItemBrowser\Export\Reducer\Combination\RecipeReducer;
use FactorioItemBrowser\Export\Reducer\Combination\RecipeReducerFactory;
use FactorioItemBrowser\ExportData\Registry\EntityRegistry;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the RecipeReducerFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Reducer\Combination\RecipeReducerFactory
 */
class RecipeReducerFactoryTest extends TestCase
{
    /**
     * Tests the invoking.
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        /* @var EntityRegistry $rawRecipeRegistry */
        $rawRecipeRegistry = $this->createMock(EntityRegistry::class);
        /* @var EntityRegistry $reducedRecipeRegistry */
        $reducedRecipeRegistry = $this->createMock(EntityRegistry::class);

        /* @var RawExportDataService|MockObject $rawExportDataService */
        $rawExportDataService = $this->getMockBuilder(RawExportDataService::class)
                                     ->setMethods(['getRecipeRegistry'])
                                     ->disableOriginalConstructor()
                                     ->getMock();
        $rawExportDataService->expects($this->once())
                             ->method('getRecipeRegistry')
                             ->willReturn($rawRecipeRegistry);

        /* @var ReducedExportDataService|MockObject $reducedExportDataService */
        $reducedExportDataService = $this->getMockBuilder(ReducedExportDataService::class)
                                         ->setMethods(['getRecipeRegistry'])
                                         ->disableOriginalConstructor()
                                         ->getMock();
        $reducedExportDataService->expects($this->once())
                                 ->method('getRecipeRegistry')
                                 ->willReturn($reducedRecipeRegistry);

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

        $factory = new RecipeReducerFactory();
        $factory($container, RecipeReducer::class);
    }
}
