<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command\Update;

use FactorioItemBrowser\Export\Command\CommandInterface;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\ModFile\ModFileManager;
use FactorioItemBrowser\Export\ModFile\ModFileReader;
use FactorioItemBrowser\ExportData\Entity\Mod;
use FactorioItemBrowser\ExportData\Registry\ModRegistry;
use Zend\Console\Adapter\AdapterInterface;
use Zend\ProgressBar\Adapter\Console;
use Zend\ProgressBar\ProgressBar;
use ZF\Console\Route;

/**
 * The command for updating the list of known mods from the directory.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class UpdateListCommand implements CommandInterface
{
    /**
     * The mod file manager.
     * @var ModFileManager
     */
    protected $modFileManager;

    /**
     * The mod file reader.
     * @var ModFileReader
     */
    protected $modFileReader;

    /**
     * The mod registry.
     * @var ModRegistry
     */
    protected $modRegistry;

    /**
     * Initializes the command.
     * @param ModFileManager $modFileManager
     * @param ModFileReader $modFileReader
     * @param ModRegistry $modRegistry
     */
    public function __construct(ModFileManager $modFileManager, ModFileReader $modFileReader, ModRegistry $modRegistry)
    {
        $this->modFileManager = $modFileManager;
        $this->modFileReader = $modFileReader;
        $this->modRegistry = $modRegistry;
    }

    /**
     * Invokes the command.
     * @param Route $route
     * @param AdapterInterface $console
     * @return int
     * @throws ExportException
     */
    public function __invoke(Route $route, AdapterInterface $console): int
    {
        $currentMods = $this->getModsFromRegistry($this->modRegistry);
        $modFileNames = $this->modFileManager->getModFileNames();

        $console->writeLine('Hashing mod files...');
        $newMods = $this->detectNewMods($modFileNames, $currentMods);

        // @todo Re-order new mods

        $console->writeLine('Persisting mods...');
        $this->setModsToRegistry($newMods, $this->modRegistry);
        $this->printChangesToConsole($console, $newMods, $currentMods);

        $console->writeLine('Done.');
        return 0;
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
        $checksum = $this->modFileReader->calculateChecksum($modFileName);
        if (isset($currentModsByChecksum[$checksum])) {
            $result = $currentModsByChecksum[$checksum];
        } else {
            $result = $this->modFileReader->read($modFileName, $checksum);
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
     * @param AdapterInterface $console
     * @param array|Mod[] $newMods
     * @param array|Mod[] $currentMods
     */
    protected function printChangesToConsole(AdapterInterface $console, array $newMods, array $currentMods): void
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
                $console->write(str_pad($newMod->getName() . ': ', 64, ' ', STR_PAD_LEFT));
                $console->write(str_pad($currentVersion, 10, ' ', STR_PAD_LEFT));
                $console->write(' -> ');
                $console->write(str_pad($newMod->getVersion(), 10, ' ', STR_PAD_RIGHT));
                $console->writeLine();
            }
        }
    }
}
