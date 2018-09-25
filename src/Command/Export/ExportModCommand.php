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
use FactorioItemBrowser\ExportData\Registry\EntityRegistry;
use FactorioItemBrowser\ExportData\Registry\ModRegistry;
use ZF\Console\Route;

/**
 * The command for exporting a mod with all its combinations.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ExportModCommand extends AbstractCommand
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

        $combination = $this->combinationCreator->createMainCombination();
        $combinationHash = $this->combinationRegistry->set($combination);
        $this->exportCombinations([$combinationHash]);

        $mod->setCombinationHashes([$combinationHash]);
        $this->modRegistry->set($mod);
        $this->modRegistry->saveMods();
        $this->runCommand(CommandName::RENDER_MOD_ICONS, [$mod->getName()], $this->console);
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
     * Exports the combination with the specified hashes in sub processes.
     * @param array|string[] $combinationHashes
     */
    protected function exportCombinations(array $combinationHashes): void
    {
        $this->runCombinationCommands(CommandName::EXPORT_COMBINATION, $combinationHashes);
        $this->runCombinationCommands(CommandName::EXPORT_REDUCE, $combinationHashes);
    }

    /**
     * Rund a command for each of the specified combination hashes.
     * @param string $commandName
     * @param array $combinationHashes
     */
    protected function runCombinationCommands(string $commandName, array $combinationHashes): void
    {
        foreach ($combinationHashes as $combinationHash) {
            $process = $this->createCommandProcess($commandName, [$combinationHash], $this->console);
            $this->processManager->addProcess($process);
        }
        $this->processManager->waitForAllProcesses();
    }
}
