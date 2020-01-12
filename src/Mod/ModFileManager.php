<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Mod;

use Exception;
use FactorioItemBrowser\Export\Entity\InfoJson;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Exception\FileNotFoundInModException;
use FactorioItemBrowser\Export\Exception\InvalidInfoJsonFileException;
use FactorioItemBrowser\Export\Exception\InvalidModFileException;
use JMS\Serializer\SerializerInterface;
use ZipArchive;

/**
 * The manager of all the mod files.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ModFileManager
{
    /**
     * The filename of the info file.
     */
    protected const FILENAME_INFO = 'info.json';

    /**
     * The regular expression used to match the mod directory.
     */
    protected const REGEXP_MOD_DIRECTORY = '#^(.*)_\d+\.\d+\.\d+/#';

    /**
     * The serializer.
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * The directory to store the mod files in.
     * @var string
     */
    protected $modsDirectory;

    /**
     * Initializes the manager.
     * @param SerializerInterface $exportSerializer
     * @param string $modsDirectory
     */
    public function __construct(SerializerInterface $exportSerializer, string $modsDirectory)
    {
        $this->serializer = $exportSerializer;
        $this->modsDirectory = $modsDirectory;
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
        if ($success !== true || $zipArchive->numFiles === 0) {
            throw new InvalidModFileException($modZipPath, 'Unable to open zip file.');
        }

        try {
            $firstStat = $zipArchive->statIndex(0);
            if ($firstStat === false || preg_match(self::REGEXP_MOD_DIRECTORY, $firstStat['name'], $match) !== 1) {
                throw new InvalidModFileException($modZipPath, 'Unable to determine mod directory.');
            }
            $modDirectory = $match[0];
            $modDirectoryLength = strlen($modDirectory);
            $modName = $match[1];

            $this->removeModDirectory($modName);

            $targetDirectory = $this->getLocalDirectory($modName);
            mkdir($targetDirectory, 0777, true);
            for ($i = 0; $i < $zipArchive->numFiles; ++$i) {
                $stat = $zipArchive->statIndex($i);
                if (
                    $stat !== false
                    && $stat['size'] > 0
                    && substr($stat['name'], 0, $modDirectoryLength) === $modDirectory
                ) {
                    $fileName = $targetDirectory . '/' . substr($stat['name'], $modDirectoryLength);
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
     * Removes the directory of the specified mod, if present.
     * @param string $modName
     * @codeCoverageIgnore Unable to rm -rf in virtual file system.
     */
    protected function removeModDirectory(string $modName): void
    {
        $modDirectory = $this->getLocalDirectory($modName);
        if (is_dir($modDirectory)) {
            exec(sprintf('rm -rf "%s"', $modDirectory));
        }
    }

    /**
     * Returns the info from the mod.
     * @param string $modName
     * @return InfoJson
     * @throws ExportException
     */
    public function getInfo(string $modName): InfoJson
    {
        $contents = $this->readFile($modName, self::FILENAME_INFO);

        try {
            return $this->serializer->deserialize($contents, InfoJson::class, 'json');
        } catch (Exception $e) {
            throw new InvalidInfoJsonFileException($modName);
        }
    }

    /**
     * Finds files of a certain mod matching a glob pattern.
     * @param string $modName
     * @param string $globPattern
     * @return array|string[]
     */
    public function findFiles(string $modName, string $globPattern): array
    {
        $modDirectory = $this->getLocalDirectory($modName) . '/';
        $modDirectoryLength = strlen($modDirectory);

        $files = $this->executeGlob($modDirectory . $globPattern);
        return array_map(function (string $value) use ($modDirectoryLength): string {
            return substr($value, $modDirectoryLength);
        }, $files);
    }

    /**
     * Executes the glob on the specified pattern.
     * @param string $pattern
     * @return array|string[]
     * @codeCoverageIgnore Cannot emulate glob with vfsStream.
     */
    protected function executeGlob(string $pattern): array
    {
        $result = glob($pattern);
        return ($result === false) ? [] : $result;
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
        $filePath = $this->getLocalDirectory($modName) . '/' . $fileName;
        if (!file_exists($filePath)) {
            throw new FileNotFoundInModException($modName, $fileName);
        }
        return (string) file_get_contents($filePath);
    }

    /**
     * Returns the directory which is used or will be used by the specified mod.
     * @param string $modName
     * @return string
     */
    public function getLocalDirectory(string $modName): string
    {
        return $this->modsDirectory . '/' . $modName;
    }
}
