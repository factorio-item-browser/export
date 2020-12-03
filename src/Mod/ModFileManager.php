<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Mod;

use Exception;
use FactorioItemBrowser\Export\Entity\InfoJson;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Exception\FileNotFoundInModException;
use FactorioItemBrowser\Export\Exception\InvalidInfoJsonFileException;
use FactorioItemBrowser\Export\Helper\ZipArchiveExtractor;
use JMS\Serializer\SerializerInterface;

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

    protected SerializerInterface $serializer;
    protected ZipArchiveExtractor $zipArchiveExtractor;
    protected string $factorioDirectory;
    protected string $modsDirectory;

    public function __construct(
        SerializerInterface $exportSerializer,
        ZipArchiveExtractor $zipArchiveExtractor,
        string $factorioDirectory,
        string $modsDirectory
    ) {
        $this->serializer = $exportSerializer;
        $this->zipArchiveExtractor = $zipArchiveExtractor;
        $this->factorioDirectory = $factorioDirectory;
        $this->modsDirectory = $modsDirectory;
    }

    /**
     * Extracts the zip file into the working directory of the mods.
     * @param string $modName
     * @param string $modZipPath
     * @throws ExportException
     */
    public function extractModZip(string $modName, string $modZipPath): void
    {
        $this->zipArchiveExtractor->extract($modZipPath, $this->getLocalDirectory($modName));
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
