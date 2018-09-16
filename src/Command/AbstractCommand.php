<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command;

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
     * @var AdapterInterface
     */
    protected $console;

    /**
     * Invokes the command.
     * @param Route $route
     * @param AdapterInterface $console
     * @return int
     */
    public function __invoke(Route $route, AdapterInterface $console): int
    {
        $this->console = $console;
        try {
            $this->execute($route);
            $exitCode = 0;
        } catch (CommandException $e) {
            $console->writeLine(str_pad('', $console->getWidth(), '-'), ColorInterface::YELLOW);
            $console->writeLine($e->getMessage(), ColorInterface::YELLOW);
            $console->writeLine(str_pad('', $console->getWidth(), '-'), ColorInterface::YELLOW);
            $exitCode = $e->getCode();
        } catch (ExportException $e) {
            $console->writeLine(str_pad('', $console->getWidth(), '-'), ColorInterface::RED);
            $console->writeLine($e->getMessage(), ColorInterface::RED);
            $console->writeLine(str_pad('', $console->getWidth(), '-'), ColorInterface::RED);
            $exitCode = 500;
        }
        return $exitCode;
    }

    /**
     * Executes the command.
     * @param Route $route
     * @throws ExportException
     * @throws CommandException
     */
    abstract protected function execute(Route $route): void;
}
