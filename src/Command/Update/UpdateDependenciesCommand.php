<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command\Update;

use FactorioItemBrowser\Export\Command\AbstractCommand;
use FactorioItemBrowser\Export\Exception\CommandException;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Mod\DependencyReader;
use FactorioItemBrowser\ExportData\Registry\ModRegistry;
use ZF\Console\Route;

/**
 * The command for updating the dependencies of the mods.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class UpdateDependenciesCommand extends AbstractCommand
{
    /**
     * The dependency reader.
     * @var DependencyReader
     */
    protected $dependencyReader;

    /**
     * The mod registry.
     * @var ModRegistry
     */
    protected $modRegistry;

    /**
     * Initializes the command.
     * @param DependencyReader $dependencyReader
     * @param ModRegistry $modRegistry
     */
    public function __construct(DependencyReader $dependencyReader, ModRegistry $modRegistry)
    {
        $this->dependencyReader = $dependencyReader;
        $this->modRegistry = $modRegistry;
    }

    /**
     * Executes the command.
     * @param Route $route
     * @throws ExportException
     */
    protected function execute(Route $route): void
    {
        $this->console->writeLine('Updating dependencies...');

        $modNames = $this->getModNames($route);
        foreach ($modNames as $modName) {
            $this->updateDependenciesOfMod($modName);
        }

        $this->modRegistry->saveMods();
    }

    /**
     * Returns the mod names to update.
     * @param Route $route
     * @return array|string[]
     */
    protected function getModNames(Route $route): array
    {
        $modName = $route->getMatchedParam('mod', '');

        if ($modName !== '') {
            $result = [$modName];
        } else {
            $result = $this->modRegistry->getAllNames();
        }
        return $result;
    }

    /**
     * Updates the dependencies of the specified mod.
     * @param string $modName
     * @throws ExportException
     */
    protected function updateDependenciesOfMod(string $modName): void
    {
        $mod = $this->modRegistry->get($modName);
        if ($mod === null) {
            throw new CommandException('Mod not known: ' . $modName, 404);
        }

        $dependencies = $this->dependencyReader->read($mod);
        $mod->setDependencies($dependencies);
        $this->modRegistry->set($mod);
    }
}
