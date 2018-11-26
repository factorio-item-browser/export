<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command\Update;

use FactorioItemBrowser\Export\Command\AbstractCommand;
use FactorioItemBrowser\Export\Command\SubCommandTrait;
use FactorioItemBrowser\Export\Constant\CommandName;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Mod\ModFileManager;
use FactorioItemBrowser\Export\Mod\ModReader;
use FactorioItemBrowser\ExportData\Entity\Mod;
use FactorioItemBrowser\ExportData\Registry\ModRegistry;
use Zend\ProgressBar\Adapter\Console;
use Zend\ProgressBar\ProgressBar;
use ZF\Console\Route;

/**
 * The command for updating the list of known mods from the directory.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class UpdateListCommand extends AbstractCommand
{
    use SubCommandTrait;

    /**
     * The mod file manager.
     * @var ModFileManager
     */
    protected $modFileManager;

    /**
     * The mod file reader.
     * @var ModReader
     */
    protected $modReader;

    /**
     * The mod registry.
     * @var ModRegistry
     */
    protected $modRegistry;

    /**
     * Initializes the command.
     * @param ModFileManager $modFileManager
     * @param ModReader $modReader
     * @param ModRegistry $modRegistry
     */
    public function __construct(ModFileManager $modFileManager, ModReader $modReader, ModRegistry $modRegistry)
    {
        $this->modFileManager = $modFileManager;
        $this->modReader = $modReader;
        $this->modRegistry = $modRegistry;
    }

    /**
     * Executes the command.
     * @param Route $route
     * @throws ExportException
     */
    protected function execute(Route $route): void
    {
        $currentMods = $this->getModsFromRegistry($this->modRegistry);
        $modFileNames = $this->modFileManager->getModFileNames();

        $this->console->writeLine('Hashing mod files...');
        $newMods = $this->detectNewMods($modFileNames, $currentMods);

        $this->console->writeLine('Persisting mods...');
        $this->setModsToRegistry($newMods, $this->modRegistry);
        $this->printChangesToConsole($newMods, $currentMods);

        $this->runCommand(CommandName::UPDATE_DEPENDENCIES, [], $this->console);
        $this->runCommand(CommandName::UPDATE_ORDER, [], $this->console);
        $this->runCommand(CommandName::EXPORT_PREPARE, [], $this->console);

        $this->console->writeLine('Done.');
    }

    /**
     * Returns all mods by their names from the registry.
     * @param ModRegistry $modRegistry
     * @return array
     */
    protected function getModsFromRegistry(ModRegistry $modRegistry): array
    {
        $result = [];
        foreach ($modRegistry->getAllNames() as $modName) {
            $mod = $modRegistry->get($modName);
            if ($mod instanceof Mod) {
                $result[$modName] = $mod;
            }
        }
        return $result;
    }

    /**
     * Detects new mods from the specified mod files.
     * @param array|string[] $modFileNames
     * @param array|Mod[] $currentMods
     * @return array|Mod[]
     * @throws ExportException
     */
    protected function detectNewMods(array $modFileNames, array $currentMods): array
    {
        $progressBar = new ProgressBar(new Console(), 0, count($modFileNames));

        $result = [];
        $currentModsByChecksum = $this->getModsByChecksum($currentMods);
        foreach ($modFileNames as $modFileName) {
            $newMod = $this->checkModFile($modFileName, $currentModsByChecksum);
            if ($newMod instanceof Mod) {
                $result[$newMod->getName()] = $newMod;
            }
            $progressBar->next();
        }
        $progressBar->finish();
        return $result;
    }

    /**
     * Returns the mods by their checksum.
     * @param array|Mod[] $mods
     * @return array|Mod[]
     */
    protected function getModsByChecksum(array $mods): array
    {
        $result = [];
        foreach ($mods as $mod) {
            $result[$mod->getChecksum()] = $mod;
        }
        return $result;
    }

    /**
     * Checks the specified mod file and returns the new mod if it is currently not known.
     * @param string $modFileName
     * @param array|Mod[] $currentModsByChecksum
     * @return Mod|null
     * @throws ExportException
     */
    protected function checkModFile(string $modFileName, array $currentModsByChecksum): ?Mod
    {
        $result = null;
        $checksum = $this->modReader->calculateChecksum($modFileName);
        if (isset($currentModsByChecksum[$checksum])) {
            $result = $currentModsByChecksum[$checksum];
        } else {
            $result = $this->modReader->read($modFileName, $checksum);
            $this->runCommand(CommandName::CLEAN_CACHE, ['mod' => $result->getName()]);
        }
        return $result;
    }

    /**
     * Sets the mods to the registry.
     * @param array|Mod[] $mods
     * @param ModRegistry $modRegistry
     */
    protected function setModsToRegistry(array $mods, ModRegistry $modRegistry): void
    {
        $currentModNames = array_flip($modRegistry->getAllNames());

        foreach ($mods as $mod) {
            $modRegistry->set($mod);
            unset($currentModNames[$mod->getName()]);
        }

        foreach ($currentModNames as $modName) {
            $modRegistry->remove($modName);
        }

        $modRegistry->saveMods();
    }

    /**
     * Prints all changed mods to the console.
     * @param array|Mod[] $newMods
     * @param array|Mod[] $currentMods
     */
    protected function printChangesToConsole(array $newMods, array $currentMods): void
    {
        foreach ($newMods as $newMod) {
            $currentMod = $currentMods[$newMod->getName()] ?? null;
            $hasChanged = true;
            $currentVersion = '';
            if ($currentMod instanceof Mod) {
                $currentVersion = $currentMod->getVersion();
                $hasChanged = $currentMod->getChecksum() !== $newMod->getChecksum();
            }

            if ($hasChanged) {
                $this->console->write($this->console->formatModName($newMod->getName(), ': '));
                $this->console->write($this->console->formatVersion($currentVersion, true));
                $this->console->write(' -> ');
                $this->console->write($this->console->formatVersion($newMod->getVersion(), false));
                $this->console->writeLine();
            }
        }
    }
}
