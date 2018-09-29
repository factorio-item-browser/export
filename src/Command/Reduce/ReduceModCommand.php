<?php

namespace FactorioItemBrowser\Export\Command\Reduce;

use FactorioItemBrowser\Export\Command\AbstractCommand;
use FactorioItemBrowser\Export\Exception\CommandException;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\ExportData\Entity\Mod;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;
use FactorioItemBrowser\ExportData\Registry\EntityRegistry;
use FactorioItemBrowser\ExportData\Registry\ModRegistry;
use ZF\Console\Route;

/**
 * The command for reducing the actual mod.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ReduceModCommand extends AbstractCommand
{
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
     * Initializes the command.
     * @param ModRegistry $rawModRegistry
     * @param EntityRegistry $reducedCombinationRegistry
     * @param ModRegistry $reducedModRegistry
     */
    public function __construct(
        ModRegistry $rawModRegistry,
        EntityRegistry $reducedCombinationRegistry,
        ModRegistry $reducedModRegistry
    ) {
        $this->rawModRegistry = $rawModRegistry;
        $this->reducedCombinationRegistry = $reducedCombinationRegistry;
        $this->reducedModRegistry = $reducedModRegistry;
    }

    /**
     * Executes the command.
     * @param Route $route
     * @throws ExportException
     * @throws CommandException
     */
    protected function execute(Route $route): void
    {
        $rawMod = $this->fetchRawMod($route->getMatchedParam('modName'));
        $reducedMod = clone($rawMod);

        $reducedMod->setCombinationHashes($this->filterCombinationHashes($rawMod->getCombinationHashes()));
        $this->reducedModRegistry->set($reducedMod);
        $this->reducedModRegistry->saveMods();
    }

    /**
     * Fetches the raw mod to the specified name.
     * @param string $modName
     * @return Mod
     * @throws CommandException
     */
    protected function fetchRawMod(string $modName): Mod
    {
        $mod = $this->rawModRegistry->get($modName);
        if (!$mod instanceof Mod) {
            throw new CommandException('Mod not known: ' . $modName, 404);
        }
        return $mod;
    }

    /**
     * Filters the combination hashes to those actually existing in a reduced version.
     * @param array|string[] $combinationHashes
     * @return array|string[]
     */
    protected function filterCombinationHashes(array $combinationHashes): array
    {
        $result = [];
        foreach ($combinationHashes as $combinationHash) {
            $combination = $this->reducedCombinationRegistry->get($combinationHash);
            if ($combination instanceof Combination) {
                $result[] = $combinationHash;
            }
        }
        return $result;
    }
}
