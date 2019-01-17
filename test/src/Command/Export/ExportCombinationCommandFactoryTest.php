<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Command\Export;

use FactorioItemBrowser\Export\Command\Export\ExportCombinationCommand;
use FactorioItemBrowser\Export\Command\Export\ExportCombinationCommandFactory;
use FactorioItemBrowser\Export\ExportData\RawExportDataService;
use FactorioItemBrowser\Export\Factorio\Instance;
use FactorioItemBrowser\Export\Parser\ParserManager;
use FactorioItemBrowser\ExportData\Registry\EntityRegistry;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the ExportCombinationCommandFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Command\Export\ExportCombinationCommandFactory
 */
class ExportCombinationCommandFactoryTest extends TestCase
{
    /**
     * Tests the invoking.
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        /* @var EntityRegistry $combinationRegistry */
        $combinationRegistry = $this->createMock(EntityRegistry::class);

        /* @var RawExportDataService|MockObject $rawExportDataService */
        $rawExportDataService = $this->getMockBuilder(RawExportDataService::class)
                                     ->setMethods(['getCombinationRegistry'])
                                     ->disableOriginalConstructor()
                                     ->getMock();
        $rawExportDataService->expects($this->once())
                             ->method('getCombinationRegistry')
                             ->willReturn($combinationRegistry);

        /* @var ContainerInterface|MockObject $container */
        $container = $this->getMockBuilder(ContainerInterface::class)
                          ->setMethods(['get'])
                          ->getMockForAbstractClass();
        $container->expects($this->exactly(3))
                  ->method('get')
                  ->withConsecutive(
                      [RawExportDataService::class],
                      [Instance::class],
                      [ParserManager::class]
                  )
                  ->willReturnOnConsecutiveCalls(
                      $rawExportDataService,
                      $this->createMock(Instance::class),
                      $this->createMock(ParserManager::class)
                  );

        $factory = new ExportCombinationCommandFactory();
        $factory($container, ExportCombinationCommand::class);
    }
}
