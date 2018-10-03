<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command\Lists;

use FactorioItemBrowser\Export\Command\AbstractCommand;
use FactorioItemBrowser\Export\Utils\ConsoleUtils;
use FactorioItemBrowser\ExportData\Entity\Mod;
use FactorioItemBrowser\ExportData\Registry\ModRegistry;
use ZF\Console\Route;

/**
 * The command for listing all mods.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ListCommand extends AbstractCommand
{
    /**
     * The mod registry containing the available mods.
     * @var ModRegistry
     */
    protected $availableModRegistry;

    /**
     * The mod registry containing the already exported mods.
     * @var ModRegistry
     */
    protected $exportedModRegistry;

    /**
     * Initializes the command.
     * @param ModRegistry $availableModRegistry
     * @param ModRegistry $exportedModRegistry
     */
    public function __construct(ModRegistry $availableModRegistry, ModRegistry $exportedModRegistry)
    {
        $this->availableModRegistry = $availableModRegistry;
        $this->exportedModRegistry = $exportedModRegistry;
    }

    /**
     * Executes the command.
     * @param Route $route
     */
    protected function execute(Route $route): void
    {
        $mods = $this->getOrderedMods();
        foreach ($mods as $mod) {
            $this->printMod($mod, $this->exportedModRegistry->get($mod->getName()));
        }
    }

    /**
     * Returns the available mods in order.
     * @return array|Mod[]
     */
    protected function getOrderedMods(): array
    {
        $result = [];
        foreach ($this->availableModRegistry->getAllNames() as $modName) {
            $mod = $this->availableModRegistry->get($modName);
            if ($mod instanceof Mod) {
                $result[$mod->getName()] = $mod;
            }
        }

        uasort($result, function (Mod $left, Mod $right): int {
            return $left->getOrder() <=> $right->getOrder();
        });

        return $result;
    }

    /**
     * Prints the mod to the console.
     * @param Mod $availableMod
     * @param Mod|null $exportedMod
     */
    protected function printMod(Mod $availableMod, ?Mod $exportedMod): void
    {
        $this->console->write(ConsoleUtils::formatModName($availableMod->getName(), ': '));
        $this->console->write(ConsoleUtils::formatVersion($availableMod->getVersion(), true));
        if ($exportedMod instanceof Mod) {
            $this->console->write(ConsoleUtils::formatVersion($exportedMod->getVersion()));
        }
        $this->console->writeLine();
    }
}