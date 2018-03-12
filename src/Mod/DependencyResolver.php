<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Mod;

use FactorioItemBrowser\ExportData\Entity\Mod;
use FactorioItemBrowser\ExportData\Service\ExportDataService;

/**
 * The class resolving the dependencies of mods.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class DependencyResolver
{
    /**
     * The export data service.
     * @var ExportDataService
     */
    protected $exportDataService;

    /**
     * The resolved mod names.
     * @var array
     */
    protected $resolvedModNames = [];

    /**
     * Initializes the dependency resolver.
     * @param ExportDataService $exportDataService
     */
    public function __construct(ExportDataService $exportDataService)
    {
        $this->exportDataService = $exportDataService;
    }

    /**
     * Resolves the dependencies of the specified mod names.
     * @param array|string[] $modNames
     * @return array|string[]
     */
    public function resolveMandatoryDependencies(array $modNames): array
    {
        $this->resolvedModNames = [];
        sort($modNames, SORT_NATURAL | SORT_FLAG_CASE);
        foreach ($modNames as $modName) {
            $this->processMod($modName, true);
        }
        return array_keys($this->resolvedModNames);
    }

    /**
     * Resolves all optional mods of the specified mod names.
     * @param array|string[] $modNames
     * @return array|string[]
     */
    public function resolveOptionalMods(array $modNames): array
    {
        $modNames = $this->resolveMandatoryDependencies($modNames);

        $this->resolvedModNames = [];
        foreach ($modNames as $modName) {
            $this->processMod($modName, false);
        }
        return array_values(array_diff(array_keys($this->resolvedModNames), $modNames));
    }

    /**
     * Processes the mod with the specified name.
     * @param string $modName
     * @param bool $isMandatory
     * @return $this
     */
    protected function processMod(string $modName, bool $isMandatory)
    {
        $mod = $this->exportDataService->getMod($modName);
        if ($mod instanceof Mod) {
            $requiredModNames = [];
            foreach ($mod->getDependencies() as $dependency) {
                if ($dependency->getIsMandatory() === $isMandatory
                    && !isset($this->resolvedModNames[$dependency->getRequiredModName()])
                ) {
                    $requiredModNames[] = $dependency->getRequiredModName();
                }
            }
            sort($requiredModNames, SORT_NATURAL | SORT_FLAG_CASE);
            foreach ($requiredModNames as $requiredModName) {
                echo $modName . ' -> ' . $requiredModName . PHP_EOL;
                $this->processMod($requiredModName, $isMandatory);
            }
            $this->resolvedModNames[$modName] = true;
        }
        return $this;
    }
}