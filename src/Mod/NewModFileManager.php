<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Mod;

use FactorioItemBrowser\Export\Exception\ExportException;
use ZipArchive;

/**
 * The manager of all the mod files.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class NewModFileManager
{
    /**
     * The directory to store the mod files in.
     * @var string
     */
    protected $workingDirectory;

    /**
     * Initializes the manager.
     * @param string $modFileManagerWorkingDirectory
     */
    public function __construct(string $modFileManagerWorkingDirectory)
    {
        $this->workingDirectory = $modFileManagerWorkingDirectory;
    }

    /**
     * Extracts the zip file into the working directory of the mods.
     * @param string $modZipPath
     * @throws ExportException
     */
    public function extractModZip(string $modZipPath): void
    {
        $zipArchive = new ZipArchive();
        $success = $zipArchive->open($modZipPath);
        if (!$success || $zipArchive->numFiles === 0) {
            throw new ExportException('Unable to read mod zip file thingy');
        }

        try {
            $firstStat = $zipArchive->statIndex(0);
            if (!preg_match('#^(.*)_\d+\.\d+\.\d+/#', $firstStat['name'], $match)) {
                throw new ExportException('Unable to determine mod name from zip archive');
            }
            $modDirectory = $match[0];
            $modDirectoryLength = strlen($modDirectory);
            $modName = $match[1];

            $targetDirectory = $this->getModDirectory($modName);
            if (is_dir($targetDirectory)) {
                exec(sprintf('rm -rf "%s"', $targetDirectory));
            }

            for ($i = 0; $i < $zipArchive->numFiles; ++$i) {
                $stat = $zipArchive->statIndex($i);
                if ($stat['size'] > 0 && substr($stat['name'], 0, $modDirectoryLength) === $modDirectory) {
                    $fileName = $targetDirectory . substr($stat['name'], $modDirectoryLength);
                    if (!is_dir(dirname($fileName))) {
                        mkdir(dirname($fileName), 0777, true);
                    }
                    file_put_contents($fileName, $zipArchive->getStream($stat['name']));
                }
            }
        } finally {
            $zipArchive->close();
        }
    }

    /**
     * Returns the version of the mod which is locally available. If the mod is not available, an empty string is
     * returned.
     * @param string $modName
     * @return string
     */
    public function getVersion(string $modName): string
    {
        $result = '';

        $fileName = $this->getModFilePath($modName, 'info.json');
        if (file_exists($fileName)) {
            $json = json_decode(file_get_contents($fileName), true);
            $result = $json['version'] ?? '';
        }

        return $result;
    }

    /**
     * Finds files of a certain mod matching a glob pattern.
     * @param string $modName
     * @param string $globPattern
     * @return array|string[]
     */
    public function findFiles(string $modName, string $globPattern): array
    {
        $modDirectory = $this->getModDirectory($modName);
        $modDirectoryLength = strlen($modDirectory);

        $result = glob($modDirectory . $globPattern);
        if ($result === false) {
            $result = [];
        }
        return array_map(function(string $value) use ($modDirectoryLength): string {
            return substr($value, $modDirectoryLength);
        }, $result);
    }

    /**
     * Reads a file from a mod, throwing an exception if it is not present.
     * @param string $modName
     * @param string $fileName
     * @return string
     * @throws ExportException
     */
    public function readFile(string $modName, string $fileName): string
    {
        $filePath = $this->getModFilePath($modName, $fileName);
        if (!file_exists($filePath)) {
            throw new ExportException(sprintf('File %s not found in mod %s.', $fileName, $modName));
        }
        return (string) file_get_contents($filePath);
    }

    /**
     * Returns the directory which is used or will be used by the specified mod.
     * @param string $modName
     * @return string
     */
    protected function getModDirectory(string $modName): string
    {
        return $this->workingDirectory . '/' . $modName . '/';
    }

    /**
     * Returns the full path to a mod file.
     * @param string $modName
     * @param string $fileName
     * @return string
     */
    protected function getModFilePath(string $modName, string $fileName): string
    {
        return $this->getModDirectory($modName) . $fileName;
    }
}
