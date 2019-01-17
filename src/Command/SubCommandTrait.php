<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command;

use FactorioItemBrowser\Export\Console\Console;
use FactorioItemBrowser\Export\Process\CommandProcess;
use Symfony\Component\Process\Process;

/**
 * The trait adding the ability to run sub-commands.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
trait SubCommandTrait
{
    /**
     * Runs the specified command.
     * @param string $commandName
     * @param array $parameters
     * @param Console|null $console
     * @return int
     */
    protected function runCommand(string $commandName, array $parameters = [], ?Console $console = null): int
    {
        $process = $this->createCommandProcess($commandName, $parameters, $console);
        $process->run();
        return (int) $process->getExitCode();
    }

    /**
     * Creates a new process to run the specified command.
     * @param string $commandName
     * @param array $parameters
     * @param Console|null $console
     * @return Process
     */
    protected function createCommandProcess(
        string $commandName,
        array $parameters = [],
        ?Console $console = null
    ): Process {
        return new CommandProcess($commandName, $parameters, $console);
    }
}
