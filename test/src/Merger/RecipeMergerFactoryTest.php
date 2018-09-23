<?php

namespace FactorioItemBrowserTest\Export\Merger;

use FactorioItemBrowser\Export\ExportData\RawExportDataService;
use FactorioItemBrowser\Export\Merger\RecipeMerger;
use FactorioItemBrowser\Export\Merger\RecipeMergerFactory;
use FactorioItemBrowser\ExportData\Registry\EntityRegistry;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the RecipeMergerFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Merger\RecipeMergerFactory
 */
class RecipeMergerFactoryTest extends TestCase
{
    /**
     * Tests the invoking.
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        /* @var EntityRegistry $recipeRegistry */
        $recipeRegistry = $this->createMock(EntityRegistry::class);

        /* @var RawExportDataService|MockObject $rawExportDataService */
        $rawExportDataService = $this->getMockBuilder(RawExportDataService::class)
                                     ->setMethods(['getRecipeRegistry'])
                                     ->disableOriginalConstructor()
                                     ->getMock();
        $rawExportDataService->expects($this->once())
                             ->method('getRecipeRegistry')
                             ->willReturn($recipeRegistry);

        /* @var ContainerInterface|MockObject $container */
        $container = $this->getMockBuilder(ContainerInterface::class)
                          ->setMethods(['get'])
                          ->getMockForAbstractClass();
        $container->expects($this->once())
                  ->method('get')
                  ->with(RawExportDataService::class)
                  ->willReturn($rawExportDataService);

        $factory = new RecipeMergerFactory();
        $factory($container, RecipeMerger::class);
    }
}
