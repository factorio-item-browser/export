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
 * The command for reducing an exported combination.
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
        $combination = $this->fetchCombination($combinationHash);
        $reducedCombination = $this->reducerManager->reduce($combination);
        if ($this->isCombinationEmpty($reducedCombination)) {
            $this->reducedCombinationRegistry->remove($combinationHash);
        } else {
            $this->reducedCombinationRegistry->set($combinationHash);
        }

        // @todo Set Combination to reduced mod
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
}
