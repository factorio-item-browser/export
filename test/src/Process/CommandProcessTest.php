<?php
/**
 * The PHPUnit test of the CommandProcess class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */

namespace FactorioItemBrowserTest\Export\Process;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Export\Console\Console;
use FactorioItemBrowser\Export\Process\CommandProcess;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Zend\Console\ColorInterface;

/**
 * The PHPUnit test of the CommandProcess class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Process\CommandProcess
 */
class CommandProcessTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $commandName = 'abc';
        $parameters = ['def' => 'ghi'];
        $command = ['jkl', 'mno'];
        $expectedCommandLine = "'jkl' 'mno'";

        /* @var CommandProcess|MockObject $process */
        $process = $this->getMockBuilder(CommandProcess::class)
                        ->setMethods(['buildCommand'])
                        ->disableOriginalConstructor()
                        ->getMock();
        $process->expects($this->once())
                ->method('buildCommand')
                ->with($commandName, $parameters)
                ->willReturn($command);

        /* @var Console $console */
        $console = $this->createMock(Console::class);

        $process->__construct($commandName, $parameters, $console);

        $this->assertSame($console, $this->extractProperty($process, 'console'));
        $this->assertStringContainsString($expectedCommandLine, $process->getCommandLine());
        $this->assertSame(['SUBCMD' => 1], $process->getEnv());
    }

    /**
     * Tests the buildCommand method.
     * @throws ReflectionException
     * @covers ::buildCommand
     */
    public function testBuildCommand(): void
    {
        $commandName = 'abc def <def> <ghi>';
        $parameters = [
            'abc' => 'jkl',
            'def' => 'mno',
            'pqr'
        ];
        $expectedResult = [
            'php',
            $_SERVER['SCRIPT_FILENAME'],
            'abc',
            'def',
            'mno',
            '<ghi>',
            '--abc=jkl',
            'pqr'
        ];

        /* @var CommandProcess|MockObject $process */
        $process = $this->getMockBuilder(CommandProcess::class)
                        ->disableOriginalConstructor()
                        ->getMock();

        $result = $this->invokeMethod($process, 'buildCommand', $commandName, $parameters);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the start method.
     * @covers ::start
     */
    public function testStart(): void
    {
        $commandLine = 'ls -l';
        $callback = function($type, $data) {
            // Nothing to do.
        };
        $newCallback = function($type, $data) {
            // Nothing to do.
        };

        /* @var Console $console */
        $console = $this->createMock(Console::class);

        /* @var CommandProcess|MockObject $process */
        $process = $this->getMockBuilder(CommandProcess::class)
                        ->setMethods(['wrapCallback'])
                        ->setConstructorArgs([$commandLine, [], $console])
                        ->getMock();
        $process->expects($this->once())
                ->method('wrapCallback')
                ->with($callback)
                ->willReturn($newCallback);

        $process->start($callback, ['foo' => 'bar']);
    }

    /**
     * Provides the data for the wrapCallback test.
     * @return array
     */
    public function provideWrapCallback(): array
    {
        return [
            [true, true, true],
            [true, false, true],
            [false, true, true],
            [false, false, false],
        ];
    }

    /**
     * Tests the wrapCallback method.
     * @param bool $withConsole
     * @param bool $withCallback
     * @param bool $expectCallback
     * @throws ReflectionException
     * @covers ::wrapCallback
     * @dataProvider provideWrapCallback
     */
    public function testWrapCallback(bool $withConsole, bool $withCallback, bool $expectCallback): void
    {
        $output1 = 'abc';
        $output2 = 'def';
        $commandLine = 'ghi';

        if ($withConsole) {
            /* @var Console|MockObject $console */
            $console = $this->getMockBuilder(Console::class)
                            ->setMethods(['writeCommand', 'write'])
                            ->disableOriginalConstructor()
                            ->getMock();
            $console->expects($this->once())
                    ->method('writeCommand')
                    ->with($commandLine);

            $console->expects($this->exactly(2))
                    ->method('write')
                    ->withConsecutive(
                        [$output1, null],
                        [$output2, ColorInterface::RED]
                    );
        } else {
            $console = null;
        }

        if ($withCallback) {
            $expectedCallbacks = [
                CommandProcess::OUT . '|' . $output1,
                CommandProcess::ERR . '|' . $output2,
            ];
            $callback = function (string $type, string $output) use (&$expectedCallbacks): void {
                $this->assertSame($type . '|' . $output, array_shift($expectedCallbacks));
            };
        } else {
            $expectedCallbacks = [];
            $callback = null;
        }

        /* @var CommandProcess|MockObject $process */
        $process = $this->getMockBuilder(CommandProcess::class)
                        ->setMethods(['getCommandLine'])
                        ->setConstructorArgs(['foo', [], $console])
                        ->getMock();
        $process->expects($this->any())
                ->method('getCommandLine')
                ->willReturn($commandLine);

        $result = $this->invokeMethod($process, 'wrapCallback', $callback);

        if ($expectCallback) {
            $result(CommandProcess::OUT, $output1);
            $result(CommandProcess::ERR, $output2);
        }
        $this->assertCount(0, $expectedCallbacks);
    }
}
