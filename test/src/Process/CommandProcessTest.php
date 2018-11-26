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
        $commandLine = 'jkl';

        /* @var CommandProcess|MockObject $process */
        $process = $this->getMockBuilder(CommandProcess::class)
                        ->setMethods(['buildCommandLine'])
                        ->disableOriginalConstructor()
                        ->getMock();
        $process->expects($this->once())
                ->method('buildCommandLine')
                ->with($commandName, $parameters)
                ->willReturn($commandLine);

        /* @var Console $console */
        $console = $this->createMock(Console::class);

        $process->__construct($commandName, $parameters, $console);

        $this->assertSame($console, $this->extractProperty($process, 'console'));
        $this->assertSame($commandLine, $process->getCommandLine());
        $this->assertSame(['SUBCMD' => 1], $process->getEnv());
    }

    /**
     * Provides the data for the buildCommandLine test.
     * @return array
     */
    public function provideBuildCommandLine(): array
    {
        return [
            ['foo', [], 'foo'],
            ['foo bar', [], 'foo bar'],
            ['foo', ['bar'], 'foo "bar"'],
            ['foo', ['abc' => 'def'], 'foo --abc="def"'],
            ['foo', ['bar', 'abc' => 'def'], 'foo "bar" --abc="def"'],
            ['foo <bar>', ['bar' => 'abc'], 'foo "abc"'],
            ['foo <bar>', ['bar' => 'abc', 'def' => 'ghi', 'jkl'], 'foo "abc" --def="ghi" "jkl"'],
        ];
    }

    /**
     * Tests the buildCommandLine method.
     * @param string $commandName
     * @param array $parameters
     * @param string $expectedCommandLinePart
     * @throws ReflectionException
     * @covers ::buildCommandLine
     * @dataProvider provideBuildCommandLine
     */
    public function testBuildCommandLine(
        string $commandName,
        array $parameters,
        string $expectedCommandLinePart
    ): void {
        $expectedCommandLine = 'php ' . $_SERVER['SCRIPT_FILENAME'] . ' ' . $expectedCommandLinePart;

        $process = new CommandProcess('');
        $result = $this->invokeMethod($process, 'buildCommandLine', $commandName, $parameters);
        $this->assertSame($expectedCommandLine, $result);
    }

    /**
     * Tests the start method.
     * @covers ::start
     */
    public function testStart(): void
    {
        $commandLine = 'ls -l';
        $callback = 'strval';
        $newCallback = 'intval';

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
