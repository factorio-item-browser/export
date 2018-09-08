<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command\Update;

use FactorioItemBrowser\Export\Command\CommandInterface;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\ModFile\DependencyReader;
use FactorioItemBrowser\ExportData\Registry\ModRegistry;
use Zend\Console\Adapter\AdapterInterface;
use ZF\Console\Route;

/**
 * The command for updating the dependencies of the mods.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class UpdateDependenciesCommand implements CommandInterface
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
     * Invokes the command.
     * @param Route $route
     * @param AdapterInterface $console
     * @return int
     * @throws ExportException
     */
    public function __invoke(Route $route, AdapterInterface $console): int
    {
        $modNames = $this->getModNames($route);
        foreach ($modNames as $modName) {
            $this->updateDependenciesOfMod($modName);
        }

        $this->modRegistry->saveMods();
        return 0;
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
            throw new ExportException('Mod not known: ' . $modName);
        }

        $dependencies = $this->dependencyReader->read($mod);
        $mod->setDependencies($dependencies);
        $this->modRegistry->set($mod);
    }
}
