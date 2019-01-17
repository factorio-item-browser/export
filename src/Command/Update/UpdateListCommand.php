<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command\Update;

use FactorioItemBrowser\Export\Command\AbstractCommand;
use FactorioItemBrowser\Export\Command\SubCommandTrait;
use FactorioItemBrowser\Export\Constant\CommandName;
use FactorioItemBrowser\Export\Constant\ParameterName;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Mod\ModFileManager;
use FactorioItemBrowser\Export\Mod\ModReader;
use FactorioItemBrowser\ExportData\Entity\Mod;
use FactorioItemBrowser\ExportData\Registry\ModRegistry;
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
        $currentMods = $this->getModsFromRegistry();
        $modFileNames = $this->modFileManager->getModFileNames();

        $this->console->writeAction('Hashing mod files');
        $newMods = $this->detectNewMods($modFileNames, $currentMods);

        $this->console->writeAction('Persisting mods');
        $this->setModsToRegistry($newMods);
        $this->printChangesToConsole($newMods, $currentMods);

        $this->runCommand(CommandName::UPDATE_DEPENDENCIES, [], $this->console);
        $this->runCommand(CommandName::UPDATE_ORDER, [], $this->console);
        $this->runCommand(CommandName::EXPORT_PREPARE, [], $this->console);
    }

    /**
     * Returns all mods by their names from the registry.
     * @return array|Mod[]
     */
    protected function getModsFromRegistry(): array
    {
        $result = [];
        foreach ($this->modRegistry->getAllNames() as $modName) {
            $mod = $this->modRegistry->get($modName);
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
        $progressBar = $this->console->createProgressBar(count($modFileNames));

        $result = [];
        $currentModsByChecksum = $this->getModsByChecksum($currentMods);
        foreach ($modFileNames as $modFileName) {
            $newMod = $this->checkModFile($modFileName, $currentModsByChecksum);
            $result[$newMod->getName()] = $newMod;
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
     * @return Mod
     * @throws ExportException
     */
    protected function checkModFile(string $modFileName, array $currentModsByChecksum): Mod
    {
        $checksum = $this->modReader->calculateChecksum($modFileName);
        if (isset($currentModsByChecksum[$checksum])) {
            $result = $currentModsByChecksum[$checksum];
        } else {
            $result = $this->modReader->read($modFileName, $checksum);
            $this->runCommand(CommandName::CLEAN_CACHE, [ParameterName::MOD_NAME => $result->getName()]);
        }
        return $result;
    }

    /**
     * Sets the mods to the registry.
     * @param array|Mod[] $mods
     */
    protected function setModsToRegistry(array $mods): void
    {
        $currentModNames = array_flip($this->modRegistry->getAllNames());

        foreach ($mods as $mod) {
            $this->modRegistry->set($mod);
            unset($currentModNames[$mod->getName()]);
        }

        foreach (array_keys($currentModNames) as $modName) {
            $this->modRegistry->remove($modName);
        }

        $this->modRegistry->saveMods();
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
                $this->console->writeLine(sprintf(
                    '%s: %s -> %s',
                    $this->console->formatModName($newMod->getName()),
                    $this->console->formatVersion($currentVersion, true),
                    $this->console->formatVersion($newMod->getVersion(), false)
                ));
            }
        }
    }
}
