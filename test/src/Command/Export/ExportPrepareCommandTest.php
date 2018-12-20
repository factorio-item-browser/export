<?php

namespace FactorioItemBrowserTest\Export\Command\Export;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Export\Command\Export\ExportPrepareCommand;
use FactorioItemBrowser\Export\Factorio\DumpInfoGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Zend\Console\Adapter\AdapterInterface;
use ZF\Console\Route;

/**
 * The PHPUnit test of the ExportPrepareCommand class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Command\Export\ExportPrepareCommand
 */
class ExportPrepareCommandTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        /* @var DumpInfoGenerator $dumpInfoGenerator */
        $dumpInfoGenerator = $this->createMock(DumpInfoGenerator::class);

        $command = new ExportPrepareCommand($dumpInfoGenerator);

        $this->assertSame($dumpInfoGenerator, $this->extractProperty($command, 'dumpInfoGenerator'));
    }

    /**
     * Tests the execute method.
     * @throws ReflectionException
     * @covers ::execute
     */
    public function testExecute(): void
    {
        /* @var AdapterInterface|MockObject $console */
        $console = $this->getMockBuilder(AdapterInterface::class)
                        ->setMethods(['writeAction'])
                        ->getMockForAbstractClass();
        $console->expects($this->once())
                ->method('writeAction')
                ->with('Generating info.json for the dump mod');

        /* @var DumpInfoGenerator|MockObject $dumpInfoGenerator */
        $dumpInfoGenerator = $this->getMockBuilder(DumpInfoGenerator::class)
                                  ->setMethods(['generate'])
                                  ->disableOriginalConstructor()
                                  ->getMock();
        $dumpInfoGenerator->expects($this->once())
                          ->method('generate');

        /* @var Route $route */
        $route = $this->createMock(Route::class);

        $command = new ExportPrepareCommand($dumpInfoGenerator);
        $this->injectProperty($command, 'console', $console);

        $this->invokeMethod($command, 'execute', $route);
    }
}
