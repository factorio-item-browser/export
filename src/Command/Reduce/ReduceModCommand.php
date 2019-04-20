<?php

namespace FactorioItemBrowser\Export\Command\Reduce;

use FactorioItemBrowser\Export\Command\AbstractModCommand;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Reducer\Mod\ModReducerManager;
use FactorioItemBrowser\ExportData\Entity\Mod;
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
     * The mod reducer manager.
     * @var ModReducerManager
     */
    protected $modReducerManager;

    /**
     * The registry of the reduced mods.
     * @var ModRegistry
     */
    protected $reducedModRegistry;

    /**
     * Initializes the command.
     * @param ModReducerManager $modReducerManager
     * @param ModRegistry $rawModRegistry
     * @param ModRegistry $reducedModRegistry
     */
    public function __construct(
        ModReducerManager $modReducerManager,
        ModRegistry $rawModRegistry,
        ModRegistry $reducedModRegistry
    ) {
        parent::__construct($rawModRegistry);

        $this->modReducerManager = $modReducerManager;
        $this->reducedModRegistry = $reducedModRegistry;
    }

    /**
     * Exports the specified mod.
     * @param Route $route
     * @param Mod $rawMod
     * @throws ExportException
     */
    protected function processMod(Route $route, Mod $rawMod): void
    {
        $this->console->writeAction('Reducing mod ' . $rawMod->getName());
        $reducedMod = $this->modReducerManager->reduce($rawMod);

        $this->reducedModRegistry->set($reducedMod);
        $this->reducedModRegistry->saveMods();
    }
}
