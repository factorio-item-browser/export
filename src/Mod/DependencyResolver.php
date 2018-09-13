<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Mod;

use FactorioItemBrowser\ExportData\Entity\Mod;
use FactorioItemBrowser\ExportData\Entity\Mod\Dependency;
use FactorioItemBrowser\ExportData\Registry\ModRegistry;

/**
 * The class resolving the dependencies of mods.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class DependencyResolver
{
    /**
     * The mod registry.
     * @var ModRegistry
     */
    protected $modRegistry;

    /**
     * The resolved mod names.
     * @var array
     */
    protected $resolvedModNames = [];

    /**
     * Initializes the dependency resolver.
     * @param ModRegistry $modRegistry
     */
    public function __construct(ModRegistry $modRegistry)
    {
        $this->modRegistry = $modRegistry;
    }

    /**
     * Resolves the dependencies of the specified mod names.
     * @param array|string[] $modNames
     * @return array|string[]
     */
    public function resolveMandatoryDependencies(array $modNames): array
    {
        $this->resolvedModNames = [];
        foreach ($this->sortModNames($modNames) as $modName) {
            $this->processMod($modNames, $modName, true);
        }
        return array_keys($this->resolvedModNames);
    }

    /**
     * Resolves all optional dependencies of the specified mod names.
     * @param array|string[] $modNames
     * @return array|string[]
     */
    public function resolveOptionalDependencies(array $modNames): array
    {
        $mandatoryModNames = $this->resolveMandatoryDependencies($modNames);

        $this->resolvedModNames = [];
        foreach ($this->sortModNames($modNames) as $modName) {
            $this->processMod($modNames, $modName, false);
        }
        return array_values(array_diff(array_keys($this->resolvedModNames), $mandatoryModNames));
    }

    /**
     * Processes the mod with the specified name.
     * @param array $allModNames
     * @param string $modName
     * @param bool $isMandatory
     * @return $this
     */
    protected function processMod(array $allModNames, string $modName, bool $isMandatory)
    {
        $mod = $this->modRegistry->get($modName);
        if ($mod instanceof Mod) {
            $requiredModNames = [];
            foreach ($mod->getDependencies() as $dependency) {
                if (!isset($this->resolvedModNames[$dependency->getRequiredModName()])
                    && ($this->isDependencyMandatory($isMandatory, $dependency, $allModNames)
                        || $this->isDependencyOptional($isMandatory, $dependency))
                ) {
                    $requiredModNames[] = $dependency->getRequiredModName();
                }
            }

            foreach ($this->sortModNames($requiredModNames) as $requiredModName) {
                $this->processMod($allModNames, $requiredModName, $isMandatory);
            }
            $this->resolvedModNames[$modName] = true;
        }
        return $this;
    }

    /**
     * Returns whether the dependency is mandatory and must be added.
     * @param bool $isMandatory
     * @param Dependency $dependency
     * @param array|string[] $allModNames
     * @return bool
     */
    protected function isDependencyMandatory(bool $isMandatory, Dependency $dependency, array $allModNames): bool
    {
        return $isMandatory
            && ($dependency->getIsMandatory() || in_array($dependency->getRequiredModName(), $allModNames, true));
    }

    /**
     * Returns whether the dependency is optional.
     * @param bool $isMandatory
     * @param Dependency $dependency
     * @return bool
     */
    protected function isDependencyOptional(bool $isMandatory, Dependency $dependency): bool
    {
        return !$isMandatory && !$dependency->getIsMandatory();
    }

    /**
     * Sorts the mod names.
     * @param array|string[] $modNames
     * @return array|string[]
     */
    protected function sortModNames(array $modNames): array
    {
        sort($modNames, SORT_NATURAL | SORT_FLAG_CASE);
        return $modNames;
    }
}
