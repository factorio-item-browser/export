<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Combination;

use FactorioItemBrowser\Export\ExportData\RawExportDataService;
use FactorioItemBrowser\Export\Combination\ParentCombinationFinder;
use FactorioItemBrowser\Export\Combination\ParentCombinationFinderFactory;
use FactorioItemBrowser\ExportData\Registry\EntityRegistry;
use FactorioItemBrowser\ExportData\Registry\ModRegistry;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the ParentCombinationFinderFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Combination\ParentCombinationFinderFactory
 */
class ParentCombinationFinderFactoryTest extends TestCase
{
    /**
     * Tests the invoking.
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        /* @var EntityRegistry $combinationRegistry */
        $combinationRegistry = $this->createMock(EntityRegistry::class);
        /* @var ModRegistry $modRegistry */
        $modRegistry = $this->createMock(ModRegistry::class);

        /* @var RawExportDataService|MockObject $rawExportDataService */
        $rawExportDataService = $this->getMockBuilder(RawExportDataService::class)
                                     ->setMethods(['getCombinationRegistry', 'getModRegistry'])
                                     ->disableOriginalConstructor()
                                     ->getMock();
        $rawExportDataService->expects($this->once())
                             ->method('getCombinationRegistry')
                             ->willReturn($combinationRegistry);
        $rawExportDataService->expects($this->once())
                             ->method('getModRegistry')
                             ->willReturn($modRegistry);


        /* @var ContainerInterface|MockObject $container */
        $container = $this->getMockBuilder(ContainerInterface::class)
                          ->setMethods(['get'])
                          ->getMockForAbstractClass();
        $container->expects($this->once())
                  ->method('get')
                  ->with(RawExportDataService::class)
                  ->willReturn($rawExportDataService);

        $factory = new ParentCombinationFinderFactory();
        $factory($container, ParentCombinationFinder::class);
    }
}
