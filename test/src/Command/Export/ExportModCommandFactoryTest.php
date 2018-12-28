<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Command\Export;

use FactorioItemBrowser\Export\Combination\CombinationCreator;
use FactorioItemBrowser\Export\Command\Export\ExportModCommand;
use FactorioItemBrowser\Export\Command\Export\ExportModCommandFactory;
use FactorioItemBrowser\Export\ExportData\RawExportDataService;
use FactorioItemBrowser\ExportData\Registry\ModRegistry;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the ExportModCommandFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Command\Export\ExportModCommandFactory
 */
class ExportModCommandFactoryTest extends TestCase
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

        /* @var ContainerInterface|MockObject $container */
        $container = $this->getMockBuilder(ContainerInterface::class)
                          ->setMethods(['get'])
                          ->getMockForAbstractClass();
        $container->expects($this->exactly(2))
                  ->method('get')
                  ->withConsecutive(
                      [CombinationCreator::class],
                      [RawExportDataService::class]
                  )
                  ->willReturnOnConsecutiveCalls(
                      $this->createMock(CombinationCreator::class),
                      $rawExportDataService
                  );

        $factory = new ExportModCommandFactory();
        $factory($container, ExportModCommand::class);
    }
}
