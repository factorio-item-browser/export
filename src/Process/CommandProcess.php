<?php

namespace FactorioItemBrowser\Export\Process;

use Symfony\Component\Process\Process;
use Zend\Console\Adapter\AdapterInterface;
use Zend\Console\ColorInterface;

/**
 * The process for executing another command of the exporter.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class CommandProcess extends Process
{
    /**
     * The console.
     * @var AdapterInterface|null
     */
    protected $console;

    /**
     * Initializes the process.
     * @param string $commandName
     * @param array $parameters
     * @param AdapterInterface|null $console
     */
    public function __construct(string $commandName, array $parameters = [], ?AdapterInterface $console = null)
    {
        parent::__construct($this->buildCommandLine($commandName, $parameters), null, ['SUBCMD' => 1], null, null);
        $this->console = $console;
    }

    /**
     * Builds the command line to use for the process.
     * @param string $commandName
     * @param array $parameters
     * @return string
     */
    protected function buildCommandLine(string $commandName, array $parameters): string
    {
        $commandParts = ['php', $_SERVER['SCRIPT_FILENAME'], $commandName];
        foreach ($parameters as $name => $value) {
            if (is_int($name)) {
                $commandParts[] = '"' . $value . '"';
            } else {
                $commandParts[] = '--' . $name . '="' . $value . '"';
            }
        }

        return implode(' ', $commandParts);
    }

    /**
     * Starts the process.
     * @param callable|null $callback
     * @param array $env
     */
    public function start(callable $callback = null, array $env = []): void
    {
        if ($this->console instanceof AdapterInterface) {
            $this->console->writeLine('Starting process: ' . $this->getCommandLine(), ColorInterface::GRAY);

            $callback = function (string $type, string $output): void {
                if ($this->console instanceof AdapterInterface) {
                    $this->console->write($output, $type === self::ERR ? ColorInterface::RED : null);
                }
            };
        }

        parent::start($callback, $env);
    }
}
