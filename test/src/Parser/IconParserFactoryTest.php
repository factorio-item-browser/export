<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Parser;

use FactorioItemBrowser\Export\ExportData\RawExportDataService;
use FactorioItemBrowser\Export\Parser\IconParser;
use FactorioItemBrowser\Export\Parser\IconParserFactory;
use FactorioItemBrowser\ExportData\Registry\EntityRegistry;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the IconParserFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Parser\IconParserFactory
 */
class IconParserFactoryTest extends TestCase
{
    /**
     * Tests the invoking.
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        /* @var EntityRegistry $iconRegistry */
        $iconRegistry = $this->createMock(EntityRegistry::class);

        /* @var RawExportDataService|MockObject $rawExportDataService */
        $rawExportDataService = $this->getMockBuilder(RawExportDataService::class)
                                     ->setMethods(['getIconRegistry'])
                                     ->disableOriginalConstructor()
                                     ->getMock();
        $rawExportDataService->expects($this->once())
                             ->method('getIconRegistry')
                             ->willReturn($iconRegistry);

        /* @var ContainerInterface|MockObject $container */
        $container = $this->getMockBuilder(ContainerInterface::class)
                          ->setMethods(['get'])
                          ->getMockForAbstractClass();
        $container->expects($this->once())
                  ->method('get')
                  ->with(RawExportDataService::class)
                  ->willReturn($rawExportDataService);

        $factory = new IconParserFactory();
        $factory($container, IconParser::class);
    }
}
