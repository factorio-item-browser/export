<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command;

use FactorioItemBrowser\Export\Constant\ParameterName;
use FactorioItemBrowser\Export\Exception\CommandException;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;
use FactorioItemBrowser\ExportData\Registry\EntityRegistry;
use ZF\Console\Route;

/**
 * The abstract class of the combination handling commands.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
abstract class AbstractCombinationCommand extends AbstractCommand
{
    /**
     * The registry of the combinations.
     * @var EntityRegistry
     */
    protected $combinationRegistry;

    /**
     * Initializes the command.
     * @param EntityRegistry $combinationRegistry
     */
    public function __construct(EntityRegistry $combinationRegistry)
    {
        $this->combinationRegistry = $combinationRegistry;
    }

    /**
     * Executes the command.
     * @param Route $route
     * @throws ExportException
     */
    protected function execute(Route $route): void
    {
        $combination = $this->fetchCombination($route->getMatchedParam(ParameterName::COMBINATION_HASH, ''));
        $this->processCombination($route, $combination);
    }

    /**
     * Exports the specified combination.
     * @param Route $route
     * @param Combination $combination
     * @throws ExportException
     */
    abstract protected function processCombination(Route $route, Combination $combination): void;

    /**
     * Fetches the combination to the specified hash.
     * @param string $combinationHash
     * @return Combination
     * @throws CommandException
     */
    protected function fetchCombination(string $combinationHash): Combination
    {
        $combination = $this->combinationRegistry->get($combinationHash);
        if (!$combination instanceof Combination) {
            throw new CommandException('Combination hash #' . $combinationHash . ' not known.', 404);
        }
        return $combination;
    }
}
