<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command\Reduce;

use FactorioItemBrowser\Export\Command\AbstractCombinationCommand;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Reducer\Combination\CombinationReducerManager;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;
use FactorioItemBrowser\ExportData\Registry\EntityRegistry;
use ZF\Console\Route;

/**
 * The command for reducing an exported combination.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ReduceCombinationCommand extends AbstractCombinationCommand
{
    /**
     * The registry of the reduced combinations.
     * @var EntityRegistry
     */
    protected $reducedCombinationRegistry;

    /**
     * The combination reducer manager.
     * @var CombinationReducerManager
     */
    protected $combinationReducerManager;

    /**
     * Initializes the command.
     * @param EntityRegistry $rawCombinationRegistry
     * @param EntityRegistry $reducedCombinationRegistry
     * @param CombinationReducerManager $combinationReducerManager
     */
    public function __construct(
        EntityRegistry $rawCombinationRegistry,
        EntityRegistry $reducedCombinationRegistry,
        CombinationReducerManager $combinationReducerManager
    ) {
        parent::__construct($rawCombinationRegistry);
        $this->reducedCombinationRegistry = $reducedCombinationRegistry;
        $this->combinationReducerManager = $combinationReducerManager;
    }

    /**
     * Exports the specified combination.
     * @param Route $route
     * @param Combination $combination
     * @throws ExportException
     */
    protected function processCombination(Route $route, Combination $combination): void
    {
        $this->console->writeAction('Reducing combination ' . $combination->getName());
        $reducedCombination = $this->combinationReducerManager->reduce($combination);

        if ($this->isCombinationEmpty($reducedCombination)) {
            $this->reducedCombinationRegistry->remove($combination->calculateHash());
        } else {
            $this->reducedCombinationRegistry->set($reducedCombination);
        }
    }

    /**
     * Checks whether the specified combination is empty.
     * @param Combination $combination
     * @return bool
     */
    protected function isCombinationEmpty(Combination $combination): bool
    {
        return count($combination->getLoadedOptionalModNames()) > 0
            && count($combination->getIconHashes()) === 0
            && count($combination->getItemHashes()) === 0
            && count($combination->getMachineHashes()) === 0
            && count($combination->getRecipeHashes()) === 0;
    }
}
