<?php

namespace FactorioItemBrowser\Export\Command\Export;

use BluePsyduck\SymfonyProcessManager\ProcessManager;
use FactorioItemBrowser\Export\Combination\CombinationCreator;
use FactorioItemBrowser\Export\Command\AbstractModCommand;
use FactorioItemBrowser\Export\Command\SubCommandTrait;
use FactorioItemBrowser\Export\Constant\CommandName;
use FactorioItemBrowser\Export\Constant\ParameterName;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\ExportData\Entity\Mod;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;
use FactorioItemBrowser\ExportData\Registry\EntityRegistry;
use FactorioItemBrowser\ExportData\Registry\ModRegistry;
use ZF\Console\Route;

/**
 * The step command of exporting a mod.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ExportModStepCommand extends AbstractModCommand
{
    use SubCommandTrait;

    /**
     * The combination creator.
     * @var CombinationCreator
     */
    protected $combinationCreator;

    /**
     * The registry of the combinations.
     * @var EntityRegistry
     */
    protected $combinationRegistry;

    /**
     * The process manager.
     * @var ProcessManager
     */
    protected $processManager;

    /**
     * Initializes the command.
     * @param CombinationCreator $combinationCreator
     * @param EntityRegistry $combinationRegistry
     * @param ModRegistry $modRegistry
     * @param ProcessManager $processManager
     */
    public function __construct(
        CombinationCreator $combinationCreator,
        EntityRegistry $combinationRegistry,
        ModRegistry $modRegistry,
        ProcessManager $processManager
    ) {
        parent::__construct($modRegistry);

        $this->combinationCreator = $combinationCreator;
        $this->combinationRegistry = $combinationRegistry;
        $this->processManager = $processManager;
    }

    /**
     * Exports the specified mod.
     * @param Route $route
     * @param Mod $mod
     * @throws ExportException
     */
    protected function processMod(Route $route, Mod $mod): void
    {
        $this->combinationCreator->setupForMod($mod);

        $combinations = $this->fetchCombinations((int) $route->getMatchedParam(ParameterName::STEP, 0));
        $combinationHashes = $this->exportCombinations($combinations);

        $mod->setCombinationHashes(array_merge($mod->getCombinationHashes(), $combinationHashes));
        $this->modRegistry->set($mod);
        $this->modRegistry->saveMods();
    }

    /**
     * Fetches the combinations to export.
     * @param int $step
     * @return array|Combination[]
     * @throws ExportException
     */
    protected function fetchCombinations(int $step): array
    {
        if ($step === 0) {
            $result = [$this->combinationCreator->createBaseCombination()];
        } else {
            $result = $this->combinationCreator->createCombinationsWithNumberOfOptionalMods($step);
        }
        return $result;
    }

    /**
     * Exports the specified combinations in sub processes and returns their hashes.
     * @param array|Combination[] $combinations
     * @return array|string[]
     */
    protected function exportCombinations(array $combinations): array
    {
        $combinationHashes = $this->persistCombinations($combinations);
        $this->runCombinationsCommand(CommandName::EXPORT_COMBINATION, $combinationHashes);
        $this->runCombinationsCommand(CommandName::REDUCE_COMBINATION, $combinationHashes);
        return $combinationHashes;
    }

    /**
     * Persists the specified combinations and returns their hashes.
     * @param array|Combination[] $combinations
     * @return array|string[]
     */
    protected function persistCombinations(array $combinations): array
    {
        $result = [];
        foreach ($combinations as $combination) {
            $result[] = $this->combinationRegistry->set($combination);
        }
        return $result;
    }

    /**
     * Runs a command for each of the specified combination hashes.
     * @param string $commandName
     * @param array $combinationHashes
     */
    protected function runCombinationsCommand(string $commandName, array $combinationHashes): void
    {
        foreach ($combinationHashes as $combinationHash) {
            $process = $this->createCommandProcess(
                $commandName,
                [ParameterName::COMBINATION_HASH => $combinationHash],
                $this->console
            );
            $this->processManager->addProcess($process);
        }
        $this->processManager->waitForAllProcesses();
    }
}
