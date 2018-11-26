<?php

namespace FactorioItemBrowser\Export\Process;

use FactorioItemBrowser\Export\Console\Console;
use Symfony\Component\Process\Process;
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
     * @var Console|null
     */
    protected $console;

    /**
     * Initializes the process.
     * @param string $commandName
     * @param array $parameters
     * @param Console|null $console
     */
    public function __construct(string $commandName, array $parameters = [], ?Console $console = null)
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
        $commandParts = [];
        foreach ($parameters as $name => $value) {
            if (is_int($name)) {
                $commandParts[] = '"' . $value . '"';
            } elseif (strpos($commandName, '<' . $name . '>') !== false) {
                $commandName = str_replace('<' . $name . '>', '"' . $value . '"', $commandName);
            } else {
                $commandParts[] = '--' . $name . '="' . $value . '"';
            }
        }
        array_unshift($commandParts, 'php', $_SERVER['SCRIPT_FILENAME'], $commandName);

        return implode(' ', $commandParts);
    }

    /**
     * Starts the process.
     * @param callable|null $callback
     * @param array $env
     */
    public function start(callable $callback = null, array $env = []): void
    {
        parent::start($this->wrapCallback($callback), $env);
    }

    /**
     * Creates the callback to print the process output into the console.
     * @param callable|null $callback
     * @return callable|null
     */
    protected function wrapCallback(?callable $callback): ?callable
    {
        $result = $callback;
        $console = $this->console;
        if ($console instanceof Console) {
            $console->writeCommand($this->getCommandLine());

            $result = function (string $type, string $output) use ($callback, $console): void {
                $console->write($output, $type === self::ERR ? ColorInterface::RED : null);
                if (is_callable($callback)) {
                    $callback($type, $output);
                }
            };
        }
        return $result;
    }
}
