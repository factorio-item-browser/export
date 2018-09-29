<?php

namespace FactorioItemBrowser\Export\Command\Export;

use FactorioItemBrowser\Export\Combination\CombinationCreator;
use FactorioItemBrowser\Export\Command\AbstractCommand;
use FactorioItemBrowser\Export\Command\SubCommandTrait;
use FactorioItemBrowser\Export\Constant\CommandName;
use FactorioItemBrowser\Export\Exception\CommandException;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\ExportData\Entity\Mod;
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
     * The registry of the mods.
     * @var ModRegistry
     */
    protected $modRegistry;

    /**
     * Initializes the command.
     * @param CombinationCreator $combinationCreator
     * @param ModRegistry $modRegistry
     */
    public function __construct(CombinationCreator $combinationCreator, ModRegistry $modRegistry)
    {
        $this->combinationCreator = $combinationCreator;
        $this->modRegistry = $modRegistry;
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

        for ($step = 0; $step <= $this->combinationCreator->getNumberOfOptionalMods(); ++$step) {
            $this->runCommand(CommandName::EXPORT_MOD_STEP, [
                'modName' => $mod->getName(),
                'step' => $step
            ], $this->console);
        }

        $this->runCommand(CommandName::REDUCE_MOD, ['modName' => $mod->getName()], $this->console);
        $this->runCommand(CommandName::RENDER_MOD_ICONS, ['modName' => $mod->getName()], $this->console);
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
}
