<?php

namespace FactorioItemBrowser\Export\Command\Reduce;

use FactorioItemBrowser\Export\Command\AbstractModCommand;
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
class ReduceModCommand extends AbstractModCommand
{
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
        parent::__construct($rawModRegistry);
        $this->reducedCombinationRegistry = $reducedCombinationRegistry;
        $this->reducedModRegistry = $reducedModRegistry;
    }

    /**
     * Exports the specified mod.
     * @param Route $route
     * @param Mod $rawMod
     */
    protected function processMod(Route $route, Mod $rawMod): void
    {
        $this->console->writeAction('Reducing mod ' . $rawMod->getName());
        $reducedMod = clone($rawMod);

        $reducedMod->setCombinationHashes($this->filterCombinationHashes($rawMod->getCombinationHashes()));
        $this->reducedModRegistry->set($reducedMod);
        $this->reducedModRegistry->saveMods();
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
