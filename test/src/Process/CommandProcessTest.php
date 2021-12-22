<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Process;

use FactorioItemBrowser\Export\Process\CommandProcess;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the CommandProcess class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Export\Process\CommandProcess
 */
class CommandProcessTest extends TestCase
{
    public function testConstruct(): void
    {
        $commandName = 'abc';
        $arguments = ['def', 'ghi'];

        $command = $_SERVER['_'] ?? 'php';
        $expectedCommandLine = "'{$command}' '{$_SERVER['SCRIPT_FILENAME']}' 'abc' 'def' 'ghi'";

        $process = new CommandProcess($commandName, $arguments);

        $this->assertNull($process->getTimeout());
        $this->assertSame($expectedCommandLine, $process->getCommandLine());
    }
}
