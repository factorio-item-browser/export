<?php

namespace FactorioItemBrowser\Export\Command\Export;

use FactorioItemBrowser\Export\Combination\CombinationCreator;
use FactorioItemBrowser\Export\Command\AbstractModCommand;
use FactorioItemBrowser\Export\Command\SubCommandTrait;
use FactorioItemBrowser\Export\Constant\CommandName;
use FactorioItemBrowser\Export\Constant\ParameterName;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\ExportData\Entity\Mod;
use FactorioItemBrowser\ExportData\Registry\ModRegistry;
use Zend\Console\ColorInterface;
use ZF\Console\Route;

/**
 * The command for exporting a mod with all its combinations.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ExportModCommand extends AbstractModCommand
{
    use SubCommandTrait;

    /**
     * The combination creator.
     * @var CombinationCreator
     */
    protected $combinationCreator;

    /**
     * Initializes the command.
     * @param CombinationCreator $combinationCreator
     * @param ModRegistry $modRegistry
     */
    public function __construct(CombinationCreator $combinationCreator, ModRegistry $modRegistry)
    {
        parent::__construct($modRegistry);

        $this->combinationCreator = $combinationCreator;
    }

    /**
     * Exports the specified mod.
     * @param Route $route
     * @param Mod $mod
     * @throws ExportException
     */
    protected function processMod(Route $route, Mod $mod): void
    {
        $this->console->writeBanner('Exporting Mod: ' . $mod->getName(), ColorInterface::LIGHT_BLUE);

        $this->combinationCreator->setupForMod($mod);
        $numberOfOptionalMods = $this->combinationCreator->getNumberOfOptionalMods();
        $this->console->writeAction('Exporting combinations in ' . ($numberOfOptionalMods + 1) . ' steps');

        for ($step = 0; $step <= $numberOfOptionalMods; ++$step) {
            $this->runCommand(CommandName::EXPORT_MOD_STEP, [
                ParameterName::MOD_NAME => $mod->getName(),
                ParameterName::STEP => $step
            ], $this->console);
        }

        $this->runCommand(CommandName::EXPORT_MOD_META, [ParameterName::MOD_NAME => $mod->getName()], $this->console);
        $this->runCommand(CommandName::REDUCE_MOD, [ParameterName::MOD_NAME => $mod->getName()], $this->console);
        $this->runCommand(CommandName::RENDER_MOD_ICONS, [ParameterName::MOD_NAME => $mod->getName()], $this->console);
    }
}
