<?php
/**
 *
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */

namespace FactorioItemBrowserTest\Export\Process;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Export\Process\CommandProcess;
use PHPUnit\Framework\TestCase;
use ReflectionException;

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
}
