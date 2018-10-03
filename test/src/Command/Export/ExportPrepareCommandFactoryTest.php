<?php

namespace FactorioItemBrowserTest\Export\Command\Export;

use FactorioItemBrowser\Export\Command\Export\ExportPrepareCommand;
use FactorioItemBrowser\Export\Command\Export\ExportPrepareCommandFactory;
use FactorioItemBrowser\Export\Factorio\DumpInfoGenerator;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the ExportPrepareCommandFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Command\Export\ExportPrepareCommandFactory
 */
class ExportPrepareCommandFactoryTest extends TestCase
{
    /**
     * Tests the invoking.
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        /* @var ContainerInterface|MockObject $container */
        $container = $this->getMockBuilder(ContainerInterface::class)
                          ->setMethods(['get'])
                          ->getMockForAbstractClass();
        $container->expects($this->once())
                  ->method('get')
                  ->with(DumpInfoGenerator::class)
                  ->willReturn($this->createMock(DumpInfoGenerator::class));

        $factory = new ExportPrepareCommandFactory();
        $factory($container, ExportPrepareCommand::class);
    }
}
