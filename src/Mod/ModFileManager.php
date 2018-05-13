<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Mod;

use BluePsyduck\Common\Data\DataContainer;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\I18n\LocaleFileReader;
use FactorioItemBrowser\Export\Utils\VersionUtils;
use FactorioItemBrowser\ExportData\Entity\Mod;
use FactorioItemBrowser\ExportData\Entity\Mod\Dependency;
use FactorioItemBrowser\ExportData\Service\ExportDataService;
use ZipArchive;

/**
 * The class managing the mod files.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ModFileManager
{
    /**
     * The regular expression used for the dependencies.
     */
    private const REGEXP_DEPENDENCY = '#^(\?)?\s*([a-zA-Z0-9\-_ ]+)\s*([<=>]*)\s*([0-9.]*)$#';

    /**
     * The regular expression used for finding locale files.
     */
    private const REGEXP_LOCALE_FILE = '#^/(locale/([a-zA-Z\-]+)/(.*)\.cfg)$#';

    /**
     * The regular expression used to detect the mods.
     */
    private const REGEXP_MOD_FILE = '#^(.+)_([0-9.]+)\.zip$#';

    /**
     * The directory containing the mods.
     * @var string
     */
    protected $modDirectory;

    /**
     * The export data service.
     * @var ExportDataService
     */
    protected $exportDataService;

    /**
     * The locale file reader.
     * @var LocaleFileReader
     */
    protected $localeFileReader;

    /**
     * The dependency resolver.
     * @var DependencyResolver
     */
    protected $dependencyResolver;

    /**
     * Initializes the mod manager.
     * @param string $modDirectory
     * @param ExportDataService $exportDataService
     * @param LocaleFileReader $localeFileReader
     * @param DependencyResolver $dependencyResolver
     */
    public function __construct(
        string $modDirectory,
        ExportDataService $exportDataService,
        LocaleFileReader $localeFileReader,
        DependencyResolver $dependencyResolver
    ) {
        $this->modDirectory = $modDirectory;
        $this->exportDataService = $exportDataService;
        $this->localeFileReader = $localeFileReader;
        $this->dependencyResolver = $dependencyResolver;
    }

    /**
     * Updates the mods from the files.
     * @return $this
     * @throws ExportException
     */
    public function updateModsFromFiles()
    {
        $this->exportDataService->loadMods();
        $modFiles = $this->detectModFiles();
        foreach ($this->exportDataService->getMods() as $mod) {
            if (isset($modFiles[$mod->getChecksum()])) {
                unset($modFiles[$mod->getChecksum()]);
            } else {
                $this->exportDataService->removeMod($mod->getName());
            }
        }

        foreach ($modFiles as $checksum => $modFileName) {
            $mod = $this->readModFile($modFileName, (string)$checksum);
            $this->exportDataService->setMod($mod);
        }

        $this->addOrderToMods();
        $this->exportDataService->saveMods();
        return $this;
    }

    /**
     * Detects all mod files currently present in the mods directory.
     * @return array
     * @throws ExportException
     */
    protected function detectModFiles()
    {
        $files = scandir($this->modDirectory);
        if ($files === false) {
            throw new ExportException('Unable to scan the mods directory: ' . $this->modDirectory);
        }

        $result = [];
        foreach ($files as $file) {
            if (preg_match(self::REGEXP_MOD_FILE, $file, $match) > 0) {
                $checksum = hash('crc32b', $this->modDirectory . '/' . $file);
                $result[$checksum] = $file;
            }
        }
        return $result;
    }

    /**
     * Reads the mod file and creates an entity from it.
     * @param string $modFileName
     * @param string $checksum
     * @return Mod
     * @throws ExportException
     */
    protected function readModFile(string $modFileName, string $checksum): Mod
    {
        $mod = new Mod();
        $mod->setFileName($modFileName)
            ->setChecksum($checksum);

        $zipArchive = new ZipArchive();
        $zipArchive->open($this->modDirectory . '/' . $modFileName);
        for ($i = 0; $i < $zipArchive->numFiles; ++$i) {
            $stats = $zipArchive->statIndex($i);
            if (substr($stats['name'], -10) === '/info.json') {
                $mod->setDirectoryName(substr($stats['name'], 0, -10));
                $this->parseInfoJson($mod);
                break;
            }
        }

        if (strlen($mod->getDirectoryName()) === 0) {
            throw new ExportException('Unable to locate the info.json file in ' . $modFileName);
        }

        return $mod;
    }

    /**
     * Parses the info.json file of the specified mod.
     * @param Mod $mod
     * @return $this
     * @throws ExportException
     */
    protected function parseInfoJson(Mod $mod)
    {
        $json = json_decode($this->getFileContents($mod, 'info.json'), true);
        if (!is_array($json)) {
            throw new ExportException('Unable to parse info.json of mod ' . $mod->getFileName());
        }

        $jsonData = new DataContainer($json);

        $mod->setName($jsonData->getString('name'))
            ->setAuthor($jsonData->getString('author'))
            ->setVersion(VersionUtils::normalize($jsonData->getString('version')));

        $mod->getTitles()->setTranslation('en', $jsonData->getString('title'));
        $mod->getDescriptions()->setTranslation('en', $jsonData->getString('description'));

        $dependencies = [];
        foreach ($jsonData->getArray('dependencies') as $dependencyString) {
            $dependency = $this->parseDependency($mod, (string) $dependencyString);
            if ($dependency instanceof Dependency) {
                $dependencies[$dependency->getRequiredModName()] = $dependency;
            }
        }
        $mod->setDependencies($dependencies);

        // Always add the base dependency, because some mods forgot it.
        if ($mod->getName() !== 'base' && !$this->hasBaseDependency($mod)) {
            $baseDependency = new Dependency();
            $baseDependency
                ->setRequiredModName('base')
                ->setIsMandatory(true)
                ->setRequiredVersion(VersionUtils::normalize(''));
            $mod->addDependency($baseDependency);
        }
        return $this;
    }

    /**
     * Returns the contents of the specified file.
     * @param Mod $mod
     * @param string $fileName
     * @return string
     * @throws ExportException
     */
    public function getFileContents(Mod $mod, string $fileName): string
    {
        $contents = @file_get_contents($this->getFullFilePath($mod, $fileName));
        if (empty($contents)) {
            throw new ExportException(
                'Unable to read file ' . $fileName . ' of mod ' . $mod->getName() ?: $mod->getFileName()
            );
        }
        return $contents;
    }

    /**
     * Returns the full file path of the specified file in the mod.
     * @param Mod $mod
     * @param string $fileName
     * @return string
     */
    protected function getFullFilePath(Mod $mod, string $fileName): string
    {
        return 'zip://' . $this->modDirectory . '/' . $mod->getFileName()
            . '#' . $mod->getDirectoryName() . '/' . $fileName;
    }

    /**
     * Parses the specified dependency string.
     * @param Mod $mod
     * @param string $dependencyString
     * @return Dependency|null
     * @throws ExportException
     */
    protected function parseDependency(Mod $mod, string $dependencyString): ?Dependency
    {
        if (preg_match(self::REGEXP_DEPENDENCY, trim($dependencyString), $match) === 0) {
            throw new ExportException(
                'Unable to parse dependency of mod ' . $mod->getName() . ': ' . $dependencyString
            );
        }

        $dependency = null;
        if ($match[3] !== '<' && $match[3] !== '>') {
            $dependency = new Dependency();
            $dependency
                ->setRequiredModName(trim($match[2]))
                ->setRequiredVersion(VersionUtils::normalize($match[4]))
                ->setIsMandatory($match[1] !== '?');
        }
        return $dependency;
    }

    /**
     * Returns whether the specified mod has the base mod as dependency.
     * @param Mod $mod
     * @return bool
     */
    protected function hasBaseDependency(Mod $mod): bool
    {
        $result = false;
        foreach ($mod->getDependencies() as $dependency) {
            if ($dependency->getRequiredModName() === 'base') {
                $dependency->setIsMandatory(true); // Let's make sure it is always required.
                $result = true;
                break;
            }
        }
        return $result;
    }

    /**
     * Adds the order values to the mods.
     * @return $this
     */
    protected function addOrderToMods()
    {
        $modNames = array_keys($this->exportDataService->getMods());
        $orderedModNames = $this->dependencyResolver->resolveMandatoryDependencies($modNames);

        $order = 1;
        foreach ($orderedModNames as $modName) {
            $mod = $this->exportDataService->getMod($modName);
            if ($mod instanceof Mod) {
                $mod->setOrder($order);
                ++$order;
            }
        }
        return $this;
    }

    /**
     * Returns the locale data of the specified mod, grouped by the locale.
     * @param Mod $mod
     * @return array|string[][]
     */
    public function getLocaleData(Mod $mod): array
    {
        $result = [];

        $zipArchive = new ZipArchive();
        $zipArchive->open($this->modDirectory . '/' . $mod->getFileName());
        for ($i = 0; $i < $zipArchive->numFiles; ++$i) {
            $stats = $zipArchive->statIndex($i);
            $fileName = substr($stats['name'], strlen($mod->getDirectoryName()));
            if (preg_match(self::REGEXP_LOCALE_FILE, $fileName, $match)) {
                $locale = $match[2];
                $result[$locale] = array_merge(
                    $result[$locale] ?? [],
                    $this->localeFileReader->read($this->getFullFilePath($mod, $match[1]))
                );
            }
        }
        return $result;
    }

    /**
     * Returns the mod with the specified name.
     * @param string $modName
     * @return Mod|null
     */
    public function getMod(string $modName): ?Mod
    {
        return $this->exportDataService->getMod($modName);
    }

    /**
     * Returns all loaded mods.
     * @return array|Mod[]
     */
    public function getMods(): array
    {
        return $this->exportDataService->getMods();
    }
}