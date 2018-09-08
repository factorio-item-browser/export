<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command;

use BluePsyduck\SymfonyProcessManager\ProcessManager;
use Symfony\Component\Process\Process;
use Zend\Console\Adapter\AdapterInterface;
use Zend\Console\ColorInterface;

/**
 * The trait adding the ability to run sub-commands.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
trait SubCommandTrait
{
    /**
     * Runs a single sub-command.
     * @param string $commandName
     * @param array $parameters
     * @param AdapterInterface|null $console
     * @return int
     */
    protected function runSubCommand(string $commandName, array $parameters = [], AdapterInterface $console = null): int
    {
        $process = $this->createProcessForSubCommand($commandName, $parameters);
        $process->start(function (string $type, string $data) use ($console): void {
            if ($console instanceof AdapterInterface) {
                $console->write($data);
            }
        });
        $process->wait();

        return (int) $process->getExitCode();
    }

    /**
     * Creates the process for running a sub-command.
     * @param string $commandName
     * @param array $parameters
     * @return Process
     */
    protected function createProcessForSubCommand(string $commandName, array $parameters): Process
    {
        $commandParts = ['php', $_SERVER['SCRIPT_FILENAME'], $commandName];
        foreach ($parameters as $name => $value) {
            if (is_int($name)) {
                $commandParts[] = '"' . $value . '"';
            } else {
                $commandParts[] = '--' . $name . '="' . $value . '"';
            }
        }

        return new Process(implode(' ', $commandParts), null, ['SUBCMD' => 1], null, null);
    }

    /**
     * Creates a process manager to use for running multiple commands in parallel.
     * @param AdapterInterface|null $console
     * @return ProcessManager
     */
    protected function createProcessManager(AdapterInterface $console = null): ProcessManager
    {
        $processManager = new ProcessManager(4); // @todo Remove magic number

        if ($console instanceof AdapterInterface) {
            $processManager->setProcessStartCallback(function (Process $process) use ($console): void {
                $console->writeLine('Starting process: ' . $process->getCommandLine(), ColorInterface::GRAY);
            });
            $processManager->setProcessFinishCallback(function (Process $process) use ($console): void {
                $console->write($process->getOutput());
                $console->write($process->getErrorOutput(), ColorInterface::RED);
            });
        }

        return $processManager;
    }
}
