<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Command\Export;

use BluePsyduck\SymfonyProcessManager\ProcessManager;
use FactorioItemBrowser\Export\Combination\CombinationCreator;
use FactorioItemBrowser\Export\Command\Export\ExportModStepCommand;
use FactorioItemBrowser\Export\Command\Export\ExportModStepCommandFactory;
use FactorioItemBrowser\Export\ExportData\RawExportDataService;
use FactorioItemBrowser\ExportData\Registry\EntityRegistry;
use FactorioItemBrowser\ExportData\Registry\ModRegistry;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the ExportModStepCommandFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Command\Export\ExportModStepCommandFactory
 */
class ExportModStepCommandFactoryTest extends TestCase
{
    /**
     * Tests the invoking.
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        /* @var RawExportDataService|MockObject $rawExportDataService */
        $rawExportDataService = $this->getMockBuilder(RawExportDataService::class)
                                     ->setMethods(['getCombinationRegistry', 'getModRegistry'])
                                     ->disableOriginalConstructor()
                                     ->getMock();
        $rawExportDataService->expects($this->once())
                             ->method('getCombinationRegistry')
                             ->willReturn($this->createMock(EntityRegistry::class));
        $rawExportDataService->expects($this->once())
                             ->method('getModRegistry')
                             ->willReturn($this->createMock(ModRegistry::class));

        /* @var ContainerInterface|MockObject $container */
        $container = $this->getMockBuilder(ContainerInterface::class)
                          ->setMethods(['get'])
                          ->getMockForAbstractClass();
        $container->expects($this->exactly(3))
                  ->method('get')
                  ->withConsecutive(
                      [CombinationCreator::class],
                      [ProcessManager::class],
                      [RawExportDataService::class]
                  )
                  ->willReturnOnConsecutiveCalls(
                      $this->createMock(CombinationCreator::class),
                      $this->createMock(ProcessManager::class),
                      $rawExportDataService
                  );

        $factory = new ExportModStepCommandFactory();
        $factory($container, ExportModStepCommand::class);
    }
}
