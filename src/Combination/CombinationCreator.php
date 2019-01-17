<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Combination;

use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Mod\DependencyResolver;
use FactorioItemBrowser\ExportData\Entity\Mod;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;
use FactorioItemBrowser\ExportData\Registry\EntityRegistry;
use FactorioItemBrowser\ExportData\Registry\ModRegistry;

/**
 * The class creating the combinations to be exported for a mod.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class CombinationCreator
{
    /**
     * The registry of the combinations.
     * @var EntityRegistry
     */
    protected $combinationRegistry;

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
     * The mod to create combinations for.
     * @var Mod
     */
    protected $mod;

    /**
     * The names of the mandatory mods.
     * @var array|string[]
     */
    protected $mandatoryModNames = [];

    /**
     * The names of the optional mods.
     * @var array|string[]
     */
    protected $optionalModNames = [];

    /**
     * The orders of the mod names.
     * @var array|int[]
     */
    protected $modOrders = [];

    /**
     * Initializes the combination creator.
     * @param EntityRegistry $combinationRegistry
     * @param DependencyResolver $dependencyResolver
     * @param ModRegistry $modRegistry
     */
    public function __construct(
        EntityRegistry $combinationRegistry,
        DependencyResolver $dependencyResolver,
        ModRegistry $modRegistry
    ) {
        $this->combinationRegistry = $combinationRegistry;
        $this->dependencyResolver = $dependencyResolver;
        $this->modRegistry = $modRegistry;
    }

    /**
     * Sets up the dependency resolver for the specified mod.
     * @param Mod $mod
     * @return $this
     */
    public function setupForMod(Mod $mod)
    {
        $this->mod = $mod;
        $this->mandatoryModNames = $this->dependencyResolver->resolveMandatoryDependencies([$mod->getName()]);
        $this->optionalModNames = $this->dependencyResolver->resolveOptionalDependencies(
            [$mod->getName()],
            $this->mandatoryModNames
        );
        $this->modOrders = $this->getOrdersOfModNames($this->optionalModNames);
        return $this;
    }

    /**
     * Returns the orders of the specified mod names.
     * @param array|string[] $modNames
     * @return array|int[]
     */
    protected function getOrdersOfModNames(array $modNames): array
    {
        $result = [];
        foreach ($modNames as $modName) {
            $mod = $this->modRegistry->get($modName);
            if ($mod instanceof Mod) {
                $result[$modName] = $mod->getOrder();
            }
        }
        return $result;
    }

    /**
     * Returns the number of optional mods the set up mod has.
     * @return int
     * @throws ExportException
     */
    public function getNumberOfOptionalMods(): int
    {
        $this->verifyMod();

        return count($this->optionalModNames);
    }

    /**
     * Creates the base combination.
     * @return Combination
     * @throws ExportException
     */
    public function createBaseCombination(): Combination
    {
        $this->verifyMod();

        return $this->createCombination([]);
    }

    /**
     * Creates the combinations with the specified number of optional mods.
     * @param int $numberOfOptionalMods
     * @return array|Combination[]
     * @throws ExportException
     */
    public function createCombinationsWithNumberOfOptionalMods(int $numberOfOptionalMods): array
    {
        $this->verifyMod();

        $result = [];
        foreach ($this->getCombinationsWithNumberOfOptionalMods($numberOfOptionalMods - 1) as $combination) {
            $result = array_merge($result, $this->createChildCombinations($combination));
        }
        return $result;
    }

    /**
     * Verifies that a valid mod has been set up.
     * @return bool
     * @throws ExportException
     */
    protected function verifyMod(): bool
    {
        if ($this->mod === null) {
            throw new ExportException('Unable to create combination without a mod.');
        }

        return true;
    }

    /**
     * Returns the combinations with the specified number of mods loaded.
     * @param int $numberOfOptionalMods
     * @return array|Combination[]
     */
    protected function getCombinationsWithNumberOfOptionalMods(int $numberOfOptionalMods): array
    {
        $result = [];
        foreach ($this->mod->getCombinationHashes() as $combinationHash) {
            $combination = $this->combinationRegistry->get($combinationHash);
            if ($combination instanceof Combination
                && count($combination->getLoadedOptionalModNames()) === $numberOfOptionalMods
            ) {
                $result[$combination->getName()] = $combination;
            }
        }
        return $result;
    }

    /**
     * Creates the child combinations having one optional mod more than the specified one.
     * @param Combination $combination
     * @return array|Combination[]
     */
    protected function createChildCombinations(Combination $combination): array
    {
        $result = [];
        $loadedOptionalModNames = $combination->getLoadedOptionalModNames();
        $requiredOrder = $this->getHighestOrderOfMods($loadedOptionalModNames);
        foreach ($this->optionalModNames as $optionalModName) {
            if (($this->modOrders[$optionalModName] ?? 0) > $requiredOrder) {
                $newCombination = $this->createCombination(array_merge($loadedOptionalModNames, [$optionalModName]));
                $result[$newCombination->getName()] = $newCombination;
            }
        }
        return $result;
    }

    /**
     * Returns the highest order of the specified mod names.
     * @param array|string[] $modNames
     * @return int
     */
    protected function getHighestOrderOfMods(array $modNames): int
    {
        $result = 0;
        foreach ($modNames as $modName) {
            $result = max($result, $this->modOrders[$modName] ?? 0);
        }
        return $result;
    }

    /**
     * Creates a new combination with the specified optional mods.
     * @param array|string[] $optionalModNames
     * @return Combination
     */
    protected function createCombination(array $optionalModNames): Combination
    {
        $combination = new Combination();
        $combination->setName(implode('-', array_merge([$this->mod->getName()], $optionalModNames)))
                    ->setMainModName($this->mod->getName())
                    ->setLoadedModNames(array_merge($this->mandatoryModNames, $optionalModNames))
                    ->setLoadedOptionalModNames($optionalModNames);

        return $combination;
    }
}
