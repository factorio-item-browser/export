<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command;

use FactorioItemBrowser\Export\Console\Console;
use FactorioItemBrowser\Export\Exception\CommandException;
use FactorioItemBrowser\Export\Exception\ExportException;
use Zend\Console\Adapter\AdapterInterface;
use Zend\Console\ColorInterface;
use ZF\Console\Route;

/**
 * The abstract base class of the commands.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
abstract class AbstractCommand implements CommandInterface
{
    /**
     * The console to use for printing information.
     * @var Console
     */
    protected $console;

    /**
     * Invokes the command.
     * @param Route $route
     * @param AdapterInterface $consoleAdapterAdapter
     * @return int
     */
    public function __invoke(Route $route, AdapterInterface $consoleAdapterAdapter): int
    {
        $this->console = $this->createConsole($consoleAdapterAdapter);
        try {
            $this->execute($route);
            $exitCode = 0;
        } catch (CommandException $e) {
            $this->console->writeBanner($e->getMessage(), ColorInterface::YELLOW);
            $exitCode = $e->getCode();
        } catch (ExportException $e) {
            $this->console->writeBanner($e->getMessage(), ColorInterface::RED);
            $exitCode = 500;
        }
        return $exitCode;
    }

    /**
     * Creates the console instance to use.
     * @param AdapterInterface $consoleAdapter
     * @return Console
     */
    protected function createConsole(AdapterInterface $consoleAdapter): Console
    {
        return new Console($consoleAdapter);
    }

    /**
     * Executes the command.
     * @param Route $route
     * @throws ExportException
     * @throws CommandException
     */
    abstract protected function execute(Route $route): void;
}
