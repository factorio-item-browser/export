<?php

namespace FactorioItemBrowser\Export\Command\Export;

use BluePsyduck\SymfonyProcessManager\ProcessManager;
use FactorioItemBrowser\Export\Combination\CombinationCreator;
use FactorioItemBrowser\Export\Command\AbstractCommand;
use FactorioItemBrowser\Export\Command\SubCommandTrait;
use FactorioItemBrowser\Export\Constant\CommandName;
use FactorioItemBrowser\Export\Exception\CommandException;
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
class ExportModStepCommand extends AbstractCommand
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
     * The registry of the mods.
     * @var ModRegistry
     */
    protected $modRegistry;

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
        $this->combinationCreator = $combinationCreator;
        $this->combinationRegistry = $combinationRegistry;
        $this->modRegistry = $modRegistry;
        $this->processManager = $processManager;
    }

    /**
     * Executes the command.
     * @param Route $route
     * @throws ExportException
     * @throws CommandException
     */
    protected function execute(Route $route): void
    {
        $mod = $this->fetchMod($route->getMatchedParam('modName', ''));
        $this->combinationCreator->setupForMod($mod);

        $combinations = $this->fetchCombinations((int) $route->getMatchedParam('step', 0));
        $combinationHashes = $this->exportCombinations($combinations);

        $mod->setCombinationHashes(array_merge($mod->getCombinationHashes(), $combinationHashes));
        $this->modRegistry->set($mod);
        $this->modRegistry->saveMods();
    }

    /**
     * Fetches the mod to the specified name.
     * @param string $modName
     * @return Mod
     * @throws CommandException
     */
    protected function fetchMod(string $modName): Mod
    {
        $mod = $this->modRegistry->get($modName);
        if (!$mod instanceof Mod) {
            throw new CommandException('Mod not known: ' . $modName, 404);
        }
        return $mod;
    }

    /**
     * Fetches the combinations to export.
     * @param int $step
     * @return array|Combination[]
     * @throws ExportException
     */
    protected function fetchCombinations(int $step): array
    {
        return $this->combinationCreator->createCombinationsWithNumberOfOptionalMods($step);
    }

    /**
     * Exports the specified combinations in sub processes and returns their hashes.
     * @param array|Combination[] $combinations
     * @return array|string[]
     */
    protected function exportCombinations(array $combinations): array
    {
        $combinationHashes = $this->getHashesToCombinations($combinations);
        $this->runCombinationCommands(CommandName::EXPORT_COMBINATION, $combinationHashes);
        $this->runCombinationCommands(CommandName::REDUCE_COMBINATION, $combinationHashes);
        return $combinationHashes;
    }

    /**
     * Returns the hashes to the specified combinations.
     * @param array|Combination[] $combinations
     * @return array|string[]
     */
    protected function getHashesToCombinations(array $combinations): array
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
    protected function runCombinationCommands(string $commandName, array $combinationHashes): void
    {
        foreach ($combinationHashes as $combinationHash) {
            $process = $this->createCommandProcess(
                $commandName,
                ['combinationHash' => $combinationHash],
                $this->console
            );
            $this->processManager->addProcess($process);
        }
        $this->processManager->waitForAllProcesses();
    }
}