<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command\Lists;

use FactorioItemBrowser\Export\Command\AbstractCommand;
use FactorioItemBrowser\Export\Utils\VersionUtils;
use FactorioItemBrowser\ExportData\Entity\Mod;
use FactorioItemBrowser\ExportData\Registry\ModRegistry;
use Zend\Console\ColorInterface;
use ZF\Console\Route;

/**
 * The command for listing missing mods which are mandatory dependencies of other mods.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ListMissingCommand extends AbstractCommand
{
    /**
     * The registry of the mods.
     * @var ModRegistry
     */
    protected $modRegistry;

    /**
     * Initializes the command.
     * @param ModRegistry $modRegistry
     */
    public function __construct(ModRegistry $modRegistry)
    {
        $this->modRegistry = $modRegistry;
    }

    /**
     * Executes the command.
     * @param Route $route
     */
    protected function execute(Route $route): void
    {
        $missingModNames = $this->checkForMissingMods();
        if (count($missingModNames) > 0) {
            $this->console->writeBanner('Missing mandatory mods:', ColorInterface::RED);
            $this->printMissingModNames($missingModNames);
        } else {
            $this->console->writeLine('There are no missing mandatory mods.', ColorInterface::GREEN);
        }
    }

    /**
     * Checks for missing mods which are mandatory.
     * @return array|string[]
     */
    protected function checkForMissingMods(): array
    {
        $result = [];
        $allModNames = $this->modRegistry->getAllNames();
        foreach ($allModNames as $modName) {
            $mod = $this->modRegistry->get($modName);
            if ($mod instanceof Mod) {
                foreach ($mod->getDependencies() as $dependency) {
                    $requiredModName = $dependency->getRequiredModName();
                    if ($dependency->getIsMandatory() && !in_array($requiredModName, $allModNames, true)) {
                        $requiredVersion = VersionUtils::getGreater(
                            $dependency->getRequiredVersion(),
                            $result[$requiredModName] ?? ''
                        );
                        $result[$requiredModName] = $requiredVersion;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Prints the missing mod names to the console.
     * @param array $missingModNames
     */
    protected function printMissingModNames(array $missingModNames): void
    {
        foreach ($missingModNames as $modName => $requiredVersion) {
            $this->console->writeLine(sprintf(
                '%s: %s',
                $this->console->formatModName($modName),
                $this->console->formatVersion($requiredVersion)
            ));
        }
    }
}
