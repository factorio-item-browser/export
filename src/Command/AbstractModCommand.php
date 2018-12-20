<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command;

use FactorioItemBrowser\Export\Exception\CommandException;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\ExportData\Entity\Mod;
use FactorioItemBrowser\ExportData\Registry\ModRegistry;
use ZF\Console\Route;

/**
 * The abstract class of the mod handling commands.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
abstract class AbstractModCommand extends AbstractCommand
{
    /**
     * The registry of the mods.
     * @var ModRegistry
     */
    protected $modRegistry;

    /**
     * Initializes the command.
     * @param ModRegistry $modRegistry
     */
    public function __construct(ModRegistry $modRegistry)
    {
        $this->modRegistry = $modRegistry;
    }

    /**
     * Executes the command.
     * @param Route $route
     * @throws ExportException
     */
    protected function execute(Route $route): void
    {
        $mod = $this->fetchMod($route->getMatchedParam('modName', ''));
        $this->processMod($route, $mod);
    }

    /**
     * Exports the specified mod.
     * @param Route $route
     * @param Mod $mod
     * @throws ExportException
     */
    abstract protected function processMod(Route $route, Mod $mod): void;

    /**
     * Fetches the mod to the specified name.
     * @param string $modName
     * @return Mod
     * @throws CommandException
     */
    protected function fetchMod(string $modName): Mod
    {
        $mod = $this->modRegistry->get($modName);
        if (!$mod instanceof Mod) {
            throw new CommandException('Mod not known: ' . $modName, 404);
        }
        return $mod;
    }
}
