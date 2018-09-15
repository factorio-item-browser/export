<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command\Export;

use FactorioItemBrowser\Export\Command\AbstractCommand;
use FactorioItemBrowser\Export\Exception\CommandException;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Reducer\ReducerManager;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;
use FactorioItemBrowser\ExportData\Registry\EntityRegistry;
use Zend\Console\Adapter\AdapterInterface;
use ZF\Console\Route;

/**
 *
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ExportReduceCommand extends AbstractCommand
{
    /**
     * The registry of raw combinations.
     * @var EntityRegistry
     */
    protected $rawCombinationRegistry;

    /**
     * The registry of reduced combinations.
     * @var EntityRegistry
     */
    protected $reducedCombinationRegistry;

    /**
     * The reducer manager.
     * @var ReducerManager
     */
    protected $reducerManager;

    /**
     * Initializes the command.
     * @param EntityRegistry $rawCombinationRegistry
     * @param EntityRegistry $reducedCombinationRegistry
     * @param ReducerManager $reducerManager
     */
    public function __construct(
        EntityRegistry $rawCombinationRegistry,
        EntityRegistry $reducedCombinationRegistry,
        ReducerManager $reducerManager
    ) {
        $this->rawCombinationRegistry = $rawCombinationRegistry;
        $this->reducedCombinationRegistry = $reducedCombinationRegistry;
        $this->reducerManager = $reducerManager;
    }

    /**
     * Executes the command.
     * @param Route $route
     * @param AdapterInterface $console
     * @throws ExportException
     * @throws CommandException
     */
    protected function execute(Route $route, AdapterInterface $console): void
    {
        $combinationHash = $route->getMatchedParam('combinationHash', '');
        /* @var Combination $combination */
        $combination = $this->rawCombinationRegistry->get($combinationHash);

//        $parentCombination = new Combination();
        $parentCombination = $this->rawCombinationRegistry->get('8260d4c484fb17ee'); // base

        $reducedCombination = clone($combination);
        $this->reducerManager->reduceCombination($reducedCombination, $parentCombination);
        $this->reducedCombinationRegistry->set($reducedCombination);
        var_dump(count($combination->getItemHashes()), count($reducedCombination->getItemHashes()));
        var_dump(count($combination->getIconHashes()), count($reducedCombination->getIconHashes()));
    }
}
