<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command;

use FactorioItemBrowser\Export\Utils\VersionUtils;
use FactorioItemBrowser\ExportData\Entity\Mod;
use FactorioItemBrowser\ExportData\Service\ExportDataService;
use Zend\Console\Adapter\AdapterInterface;
use Zend\Console\ColorInterface;
use ZF\Console\Route;

/**
 * The command showing missing mods which are dependencies of other mods.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ListMissingCommand implements CommandInterface
{
    /**
     * The export data service.
     * @var ExportDataService
     */
    protected $exportDataService;

    /**
     * Initializes the command.
     * @param ExportDataService $exportDataService
     */
    public function __construct(ExportDataService $exportDataService)
    {
        $this->exportDataService = $exportDataService;
    }

    /**
     * Invokes the command.
     * @param Route $route
     * @param AdapterInterface $console
     */
    public function __invoke(Route $route, AdapterInterface $console)
    {
        $missingMandatoryMods = [];
        $missingOptionalMods = [];
        $outdatedMods = [];

        foreach ($this->exportDataService->getMods() as $mod) {
            foreach ($mod->getDependencies() as $dependency) {
                $requiredMod = $this->exportDataService->getMod($dependency->getRequiredModName());
                if (!$requiredMod instanceof Mod) {
                    if ($dependency->getIsMandatory()) {
                        $missingMandatoryMods[$dependency->getRequiredModName()] = $dependency->getRequiredVersion();
                    } else {
                        $missingOptionalMods[$dependency->getRequiredModName()] = $dependency->getRequiredVersion();
                    }
                } elseif (VersionUtils::getGreater($dependency->getRequiredVersion(), $requiredMod->getVersion())
                    !== $requiredMod->getVersion()
                ) {
                    $outdatedMods[$dependency->getRequiredModName()] = $dependency->getRequiredVersion();
                }
            }
        }

        $this->printListOfMods($console, $missingMandatoryMods, 'MISSING ', ColorInterface::RED)
             ->printListOfMods($console, $outdatedMods, 'OUTDATED', ColorInterface::YELLOW)
             ->printListOfMods($console, $missingOptionalMods, 'OPTIONAL', ColorInterface::LIGHT_BLUE);
    }

    /**
     * Prints a list of mods.
     * @param AdapterInterface $console
     * @param array $modNames
     * @param string $label
     * @param int $color
     * @return $this
     */
    protected function printListOfMods(AdapterInterface $console, array $modNames, string $label, int $color)
    {
        ksort($modNames, SORT_STRING | SORT_FLAG_CASE);
        foreach ($modNames as $modName => $version) {
            $console->write(str_pad($modName . ': ', 64, ' ', STR_PAD_LEFT));
            $console->write($label, $color);
            $console->write(' ' . str_pad($version, 10, ' ', STR_PAD_RIGHT));

            $mod = $this->exportDataService->getMod($modName);
            if ($mod instanceof Mod) {
                $console->write(' current: ' . str_pad($mod->getVersion(), 10, ' ', STR_PAD_RIGHT));
            }
            $console->writeLine();
        }
        return $this;
    }
}