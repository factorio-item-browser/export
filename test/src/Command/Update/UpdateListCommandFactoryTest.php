<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Command\Update;

use FactorioItemBrowser\Export\Command\Update\UpdateListCommand;
use FactorioItemBrowser\Export\Command\Update\UpdateListCommandFactory;
use FactorioItemBrowser\Export\ExportData\RawExportDataService;
use FactorioItemBrowser\Export\ModFile\ModFileManager;
use FactorioItemBrowser\Export\ModFile\ModFileReader;
use FactorioItemBrowser\ExportData\Registry\ModRegistry;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the UpdateListCommandFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Command\Update\UpdateListCommandFactory
 */
class UpdateListCommandFactoryTest extends TestCase
{
    /**
     * Tests the invoking.
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        /* @var ModRegistry $modRegistry */
        $modRegistry = $this->createMock(ModRegistry::class);

        /* @var RawExportDataService|MockObject $rawExportDataService */
        $rawExportDataService = $this->getMockBuilder(RawExportDataService::class)
                                     ->setMethods(['getModRegistry'])
                                     ->disableOriginalConstructor()
                                     ->getMock();
        $rawExportDataService->expects($this->once())
                             ->method('getModRegistry')
                             ->willReturn($modRegistry);


        /* @var ContainerInterface|MockObject $container */
        $container = $this->getMockBuilder(ContainerInterface::class)
                          ->setMethods(['get'])
                          ->getMockForAbstractClass();
        $container->expects($this->exactly(3))
                  ->method('get')
                  ->withConsecutive(
                      [ModFileManager::class],
                      [ModFileReader::class],
                      [RawExportDataService::class]
                  )
                  ->willReturnOnConsecutiveCalls(
                      $this->createMock(ModFileManager::class),
                      $this->createMock(ModFileReader::class),
                      $rawExportDataService
                  );

        $factory = new UpdateListCommandFactory();
        $factory($container, UpdateListCommand::class);
    }
}
