<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Command;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Export\Command\SubCommandTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Symfony\Component\Process\Process;

/**
 * The PHPUnit test of the SubCommandTrait class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Command\SubCommandTrait
 */
class SubCommandTraitTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Provides the data for the createProcessForSubCommand test.
     * @return array
     */
    public function provideCreateProcessForSubCommand(): array
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
     * Tests the createProcessForSubCommand method.
     * @param string $commandName
     * @param array $parameters
     * @param string $expectedCommandLinePart
     * @throws ReflectionException
     * @covers ::createProcessForSubCommand
     * @dataProvider provideCreateProcessForSubCommand
     */
    public function testCreateProcessForSubCommand(
        string $commandName,
        array $parameters,
        string $expectedCommandLinePart
    ): void {
        $expectedCommandLine = 'php ' . $_SERVER['SCRIPT_FILENAME'] . ' ' . $expectedCommandLinePart;

        /* @var SubCommandTrait|MockObject $trait */
        $trait = $this->getMockBuilder(SubCommandTrait::class)
                      ->getMockForTrait();

        /* @var Process $process */
        $process = $this->invokeMethod($trait, 'createProcessForSubCommand', $commandName, $parameters);
        $this->assertSame($expectedCommandLine, $process->getCommandLine());
        $this->assertEquals(['SUBCMD' => 1], $process->getEnv());
    }
}
