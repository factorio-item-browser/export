<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Command\Render;

use BluePsyduck\SymfonyProcessManager\ProcessManager;
use FactorioItemBrowser\Export\Command\Render\RenderModIconsCommand;
use FactorioItemBrowser\Export\Command\Render\RenderModIconsCommandFactory;
use FactorioItemBrowser\Export\ExportData\ReducedExportDataService;
use FactorioItemBrowser\ExportData\Registry\EntityRegistry;
use FactorioItemBrowser\ExportData\Registry\ModRegistry;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the RenderModIconsCommandFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Command\Render\RenderModIconsCommandFactory
 */
class RenderModIconsCommandFactoryTest extends TestCase
{
    /**
     * Tests the invoking.
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        /* @var ReducedExportDataService|MockObject $reducedExportDataService */
        $reducedExportDataService = $this->getMockBuilder(ReducedExportDataService::class)
                                         ->setMethods(['getCombinationRegistry', 'getModRegistry'])
                                         ->disableOriginalConstructor()
                                         ->getMock();
        $reducedExportDataService->expects($this->once())
                                 ->method('getCombinationRegistry')
                                 ->willReturn($this->createMock(EntityRegistry::class));
        $reducedExportDataService->expects($this->once())
                                 ->method('getModRegistry')
                                 ->willReturn($this->createMock(ModRegistry::class));

        /* @var ContainerInterface|MockObject $container */
        $container = $this->getMockBuilder(ContainerInterface::class)
                          ->setMethods(['get'])
                          ->getMockForAbstractClass();
        $container->expects($this->exactly(2))
                  ->method('get')
                  ->withConsecutive(
                      [ProcessManager::class],
                      [ReducedExportDataService::class]
                  )
                  ->willReturnOnConsecutiveCalls(
                      $this->createMock(ProcessManager::class),
                      $reducedExportDataService
                  );

        $factory = new RenderModIconsCommandFactory();
        $factory($container, RenderModIconsCommand::class);
    }

}
