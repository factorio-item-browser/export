<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Reducer\Mod;

use FactorioItemBrowser\Export\ExportData\ReducedExportDataService;
use FactorioItemBrowser\Export\Reducer\Mod\CombinationReducer;
use FactorioItemBrowser\Export\Reducer\Mod\CombinationReducerFactory;
use FactorioItemBrowser\ExportData\Registry\EntityRegistry;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the CombinationReducerFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Reducer\Mod\CombinationReducerFactory
 */
class CombinationReducerFactoryTest extends TestCase
{
    /**
     * Tests the invoking.
     * @throws ReflectionException
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        /* @var EntityRegistry&MockObject $combinationRegistry */
        $combinationRegistry = $this->createMock(EntityRegistry::class);

        $expectedResult = new CombinationReducer($combinationRegistry);

        /* @var ReducedExportDataService&MockObject $reducedExportDataService */
        $reducedExportDataService = $this->createMock(ReducedExportDataService::class);
        $reducedExportDataService->expects($this->once())
                                 ->method('getCombinationRegistry')
                                 ->willReturn($combinationRegistry);

        /* @var ContainerInterface&MockObject $container */
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
                  ->method('get')
                  ->with($this->identicalTo(ReducedExportDataService::class))
                  ->willReturn($reducedExportDataService);

        $factory = new CombinationReducerFactory();
        $result = $factory($container, CombinationReducer::class);

        $this->assertEquals($expectedResult, $result);
    }
}
