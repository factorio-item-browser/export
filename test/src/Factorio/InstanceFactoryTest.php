<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Factorio;

use FactorioItemBrowser\Export\ExportData\RawExportDataService;
use FactorioItemBrowser\Export\Factorio\DumpExtractor;
use FactorioItemBrowser\Export\Factorio\Instance;
use FactorioItemBrowser\Export\Factorio\InstanceFactory;
use FactorioItemBrowser\ExportData\Registry\ModRegistry;
use FactorioItemBrowser\ExportData\Service\ExportDataService;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the InstanceFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Factorio\InstanceFactory
 */
class InstanceFactoryTest extends TestCase
{
    /**
     * Tests the invoking.
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        $config = [
            'factorio' => [
                'directory' => 'abc',
            ]
        ];

        /* @var ModRegistry $modRegistry */
        $modRegistry = $this->createMock(ModRegistry::class);

        /* @var ExportDataService|MockObject $rawExportDataService */
        $rawExportDataService = $this->getMockBuilder(ExportDataService::class)
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
                      ['config'],
                      [DumpExtractor::class],
                      [RawExportDataService::class]
                  )
                  ->willReturnOnConsecutiveCalls(
                      $config,
                      $this->createMock(DumpExtractor::class),
                      $rawExportDataService
                  );

        $factory = new InstanceFactory();
        $factory($container, Instance::class);
    }
}
