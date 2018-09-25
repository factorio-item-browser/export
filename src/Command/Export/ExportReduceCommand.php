<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command\Export;

use FactorioItemBrowser\Export\Command\AbstractCommand;
use FactorioItemBrowser\Export\Exception\CommandException;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Reducer\ReducerManager;
use FactorioItemBrowser\ExportData\Entity\Mod;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;
use FactorioItemBrowser\ExportData\Registry\EntityRegistry;
use FactorioItemBrowser\ExportData\Registry\ModRegistry;
use ZF\Console\Route;

/**
 * The command for reducing an exported combination.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ExportReduceCommand extends AbstractCommand
{
    /**
     * The registry of the raw combinations.
     * @var EntityRegistry
     */
    protected $rawCombinationRegistry;

    /**
     * The registry of the raw mods.
     * @var ModRegistry
     */
    protected $rawModRegistry;

    /**
     * The registry of the reduced combinations.
     * @var EntityRegistry
     */
    protected $reducedCombinationRegistry;

    /**
     * The registry of the reduced mods.
     * @var ModRegistry
     */
    protected $reducedModRegistry;

    /**
     * The reducer manager.
     * @var ReducerManager
     */
    protected $reducerManager;

    /**
     * Initializes the command.
     * @param EntityRegistry $rawCombinationRegistry
     * @param ModRegistry $rawModRegistry
     * @param EntityRegistry $reducedCombinationRegistry
     * @param ModRegistry $reducedModRegistry
     * @param ReducerManager $reducerManager
     */
    public function __construct(
        EntityRegistry $rawCombinationRegistry,
        ModRegistry $rawModRegistry,
        EntityRegistry $reducedCombinationRegistry,
        ModRegistry $reducedModRegistry,
        ReducerManager $reducerManager
    ) {
        $this->rawCombinationRegistry = $rawCombinationRegistry;
        $this->rawModRegistry = $rawModRegistry;
        $this->reducedCombinationRegistry = $reducedCombinationRegistry;
        $this->reducedModRegistry = $reducedModRegistry;
        $this->reducerManager = $reducerManager;
    }

    /**
     * Executes the command.
     * @param Route $route
     * @throws ExportException
     * @throws CommandException
     */
    protected function execute(Route $route): void
    {
        $combinationHash = $route->getMatchedParam('combinationHash', '');
        $combination = $this->fetchCombination($combinationHash);
        $reducedMod = $this->fetchReducedMod($combination->getMainModName());
        $reducedCombination = $this->reducerManager->reduce($combination);

        if ($this->isCombinationEmpty($reducedCombination)) {
            $this->reducedCombinationRegistry->remove($combinationHash);
            $this->removeCombinationHashFromMod($reducedMod, $combinationHash);
        } else {
            $this->reducedCombinationRegistry->set($reducedCombination);
            $reducedMod->addCombinationHash($combinationHash);
        }
        $this->reducedModRegistry->set($reducedMod);
        $this->reducedModRegistry->saveMods();
    }

    /**
     * Fetches the combination with the specified hash.
     * @param string $combinationHash
     * @return Combination
     * @throws CommandException
     */
    protected function fetchCombination(string $combinationHash): Combination
    {
        $combination = $this->rawCombinationRegistry->get($combinationHash);
        if (!$combination instanceof Combination) {
            throw new CommandException('Cannot find combination with hash #' . $combinationHash);
        }

        return $combination;
    }

    /**
     * Fetches the reduced mod with the specified name.
     * @param string $modName
     * @return Mod
     * @throws CommandException
     */
    protected function fetchReducedMod(string $modName): Mod
    {
        $mod = $this->reducedModRegistry->get($modName);
        if ($mod === null) {
            $mod = $this->createReducedMod($modName);
            $this->reducedModRegistry->set($mod);
            $this->reducedModRegistry->saveMods();
        }
        return $mod;
    }

    /**
     * Creates the reduced mod with the specified name.
     * @param string $modName
     * @return Mod
     * @throws CommandException
     */
    protected function createReducedMod(string $modName): Mod
    {
        $mod = $this->rawModRegistry->get($modName);
        if ($mod === null) {
            throw new CommandException('Mod not known: ' . $modName, 404);
        }

        $reducedMod = clone($mod);
        $reducedMod->setCombinationHashes([]);
        return $reducedMod;
    }

    /**
     * Checks whether the specified combination is empty.
     * @param Combination $combination
     * @return bool
     */
    protected function isCombinationEmpty(Combination $combination): bool
    {
        return count($combination->getIconHashes()) === 0
            && count($combination->getItemHashes()) === 0
            && count($combination->getMachineHashes()) === 0
            && count($combination->getRecipeHashes()) === 0;
    }

    /**
     * Removes the combination hash from the mod.
     * @param Mod $mod
     * @param string $combinationHash
     */
    protected function removeCombinationHashFromMod(Mod $mod, string $combinationHash): void
    {
        $combinationHashes = $mod->getCombinationHashes();
        $index = array_search($combinationHash, $combinationHashes, true);
        if ($index !== false) {
            unset($combinationHashes[$index]);
        }
        $mod->setCombinationHashes($combinationHashes);
    }
}
