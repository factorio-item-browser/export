<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command\Export;

use FactorioItemBrowser\Export\Command\AbstractCommand;
use FactorioItemBrowser\Export\Command\SubCommandTrait;
use FactorioItemBrowser\Export\Constant\CommandName;
use FactorioItemBrowser\Export\Mod\DependencyResolver;
use FactorioItemBrowser\ExportData\Registry\ModRegistry;
use ZF\Console\Route;

/**
 * The command for exporting a mod and all mods having it as dependency.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ExportModWithDependenciesCommand extends AbstractCommand
{
    use SubCommandTrait;

    /**
     * The dependency resolver.
     * @var DependencyResolver
     */
    protected $dependencyResolver;

    /**
     * The registry of the mods.
     * @var ModRegistry
     */
    protected $modRegistry;

    /**
     * Initializes the command.
     * @param DependencyResolver $dependencyResolver
     * @param ModRegistry $modRegistry
     */
    public function __construct(DependencyResolver $dependencyResolver, ModRegistry $modRegistry)
    {
        $this->dependencyResolver = $dependencyResolver;
        $this->modRegistry = $modRegistry;
    }

    /**
     * Executes the command.
     * @param Route $route
     */
    protected function execute(Route $route): void
    {
        $modName = $route->getMatchedParam('modName', '');
        $modNamesToExport = $this->getModNamesToExport($modName);
        $this->console->writeLine('Exporting ' . count($modNamesToExport) . ' mods...');

        $sortedModNames = $this->sortModNames($modNamesToExport);
        $this->runSubCommands($sortedModNames);
    }

    /**
     * Returns the mod names to actually export.
     * @param string $baseModName
     * @return array|string[]
     */
    protected function getModNamesToExport(string $baseModName): array
    {
        $result = [];
        foreach ($this->modRegistry->getAllNames() as $modName) {
            if ($modName === $baseModName || $this->hasDependency($baseModName, $modName)) {
                $result[] = $modName;
            }
        }
        return $result;
    }

    /**
     * Checks whether a mod is a dependency of another one.
     * @param string $requiredModName
     * @param string $modNameToCheck
     * @return bool
     */
    protected function hasDependency(string $requiredModName, string $modNameToCheck): bool
    {
        $mandatoryDependencies = $this->dependencyResolver->resolveMandatoryDependencies([$modNameToCheck]);
        if (in_array($requiredModName, $mandatoryDependencies, true)) {
            $result = true;
        } else {
            $optionalDependencies = $this->dependencyResolver->resolveOptionalDependencies(
                [$modNameToCheck],
                $mandatoryDependencies
            );
            $result = in_array($requiredModName, $optionalDependencies, true);
        }
        return $result;
    }

    /**
     * Sorts the mod names.
     * @param array|string[] $modNames
     * @return array|string[]
     */
    protected function sortModNames(array $modNames): array
    {
        $dependencies = $this->dependencyResolver->resolveMandatoryDependencies($modNames);
        return array_intersect($dependencies, $modNames);
    }

    /**
     * Runs the sub commands with the specified mod names.
     * @param array|string[] $modNames
     */
    protected function runSubCommands(array $modNames): void
    {
        foreach ($modNames as $modName) {
            $this->runCommand(
                CommandName::EXPORT_MOD,
                ['modName' => $modName],
                $this->console
            );
        }
    }
}
