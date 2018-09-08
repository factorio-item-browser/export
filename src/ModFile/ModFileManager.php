<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\ModFile;

use BluePsyduck\Common\Data\DataContainer;
use FactorioItemBrowser\Export\Cache\ModFileCache;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\ExportData\Entity\Mod;

/**
 * The manager class of the mod files.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ModFileManager
{
    /**
     * The regular expression used to detect the mods.
     */
    protected const REGEXP_MOD_FILE = '#^(.+)_([0-9.]+)\.zip$#';

    /**
     * The cache to use.
     * @var ModFileCache
     */
    protected $cache;

    /**
     * The directory containing the mods.
     * @var string
     */
    protected $directory;

    /**
     * Initializes the manager.
     * @param ModFileCache $cache
     * @param string $directory
     */
    public function __construct(ModFileCache $cache, string $directory)
    {
        $this->cache = $cache;
        $this->directory = $directory;
    }

    /**
     * Returns the specified file from the mod. Throws an exception if the file is not found.
     * @param Mod $mod
     * @param string $fileName
     * @param bool $ignoreCache
     * @return string
     * @throws ExportException
     */
    public function getFile(Mod $mod, string $fileName, bool $ignoreCache = false): string
    {
        $result = $ignoreCache ? null : $this->cache->read($mod->getName(), $fileName);
        if ($result === null) {
            $result = $this->readFile($mod, $fileName);
            if ($result === null) {
                throw new ExportException('Unable to read file ' . $fileName . ' of mod ' . $mod->getName());
            }
            $this->cache->write($mod->getName(), $fileName, $result);
        }
        return $result;
    }

    /**
     * Reads a file from the specified mod.
     * @param Mod $mod
     * @param string $fileName
     * @return string|null
     */
    public function readFile(Mod $mod, string $fileName): ?string
    {
        $filePath = $this->getFullFilePath($mod, $fileName);
        $content = @file_get_contents($filePath);
        return is_string($content) ? $content : null;
    }

    /**
     * Returns the full path of the file in the mod's zip.
     * @param Mod $mod
     * @param string $fileName
     * @return string
     */
    protected function getFullFilePath(Mod $mod, string $fileName): string
    {
        return 'zip://' . $this->directory . '/' . $mod->getFileName()
            . '#' . $mod->getDirectoryName() . '/' . $fileName;
    }

    /**
     * Returns the info.json file from the specified mod.
     * @param Mod $mod
     * @param bool $ignoreCache
     * @return DataContainer
     * @throws ExportException
     */
    public function getInfoJson(Mod $mod, bool $ignoreCache = false): DataContainer
    {
        $result = null;
        $content = $this->getFile($mod, 'info.json', $ignoreCache);
        $json = json_decode($content, true);
        if (is_array($json)) {
            $result = new DataContainer($json);
        }

        if ($result === null) {
            throw new ExportException('Unable to parse info.json of mod ' . $mod->getFileName());
        }
        return $result;
    }

    /**
     * Returns the file names of all the mods in the directory.
     * @return array|string[]
     * @throws ExportException
     */
    public function getModFileNames(): array
    {
        $files = @scandir($this->directory);
        if ($files === false) {
            throw new ExportException('Unable to scan the mods directory: ' . $this->directory);
        }

        $result = [];
        foreach ($files as $file) {
            if (preg_match(self::REGEXP_MOD_FILE, $file) > 0) {
                $result[] = $this->directory . DIRECTORY_SEPARATOR . $file;
            }
        }
        return $result;
    }
}
