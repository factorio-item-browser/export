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
     * The default mods actually shipped with Factorio.
     */
    protected const DEFAULT_MODS = [
        'base',
        'core',
    ];

    /**
     * The serializer.
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * The directory of the Factorio game itself.
     * @var string
     */
    protected $factorioDirectory;

    /**
     * The directory to store the mod files in.
     * @var string
     */
    protected $modsDirectory;

    /**
     * Initializes the manager.
     * @param SerializerInterface $exportSerializer
     * @param string $factorioDirectory
     * @param string $modsDirectory
     */
    public function __construct(SerializerInterface $exportSerializer, string $factorioDirectory, string $modsDirectory)
    {
        $this->serializer = $exportSerializer;
        $this->factorioDirectory = $factorioDirectory;
        $this->modsDirectory = $modsDirectory;
    }

    /**
     * Extracts the zip file into the working directory of the mods.
     * @param string $modName
     * @param string $modZipPath
     * @throws InvalidModFileException
     */
    public function extractModZip(string $modName, string $modZipPath): void
    {
        $zipArchive = new ZipArchive();
        $success = $zipArchive->open($modZipPath);
        if ($success !== true || $zipArchive->numFiles === 0) {
            throw new InvalidModFileException($modZipPath, 'Unable to open zip file.');
        }

        try {
            $this->removeModDirectory($modName);

            $targetDirectory = $this->getLocalDirectory($modName);
            mkdir($targetDirectory, 0777, true);
            for ($i = 0; $i < $zipArchive->numFiles; ++$i) {
                $stat = $zipArchive->statIndex($i);

                if (
                    $stat !== false
                    && substr($stat['name'], -1) !== '/' // Ignore directories
                    && strpos($stat['name'], '/') !== false
                ) {
                    $fileName = $targetDirectory . substr($stat['name'], strpos($stat['name'], '/'));

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
        if (in_array($modName, self::DEFAULT_MODS, true)) {
            return $this->factorioDirectory . '/data/' . $modName;
        }
        return $this->modsDirectory . '/' . $modName;
    }
}
