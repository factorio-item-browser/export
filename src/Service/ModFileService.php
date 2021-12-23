<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Service;

use BluePsyduck\LaminasAutoWireFactory\Attribute\Alias;
use Exception;
use FactorioItemBrowser\Export\AutoWire\Attribute\ReadPathFromConfig;
use FactorioItemBrowser\Export\Constant\ConfigKey;
use FactorioItemBrowser\Export\Constant\ServiceName;
use FactorioItemBrowser\Export\Entity\InfoJson;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Exception\FileNotFoundInModException;
use FactorioItemBrowser\Export\Exception\InternalException;
use FactorioItemBrowser\Export\Exception\InvalidInfoJsonFileException;
use FactorioItemBrowser\Export\Helper\ZipArchiveExtractor;
use JMS\Serializer\SerializerInterface;

/**
 * The service managing the files of the mods.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ModFileService
{
    private const FILENAME_INFO = 'info.json';
    private const VANILLA_MODS = [
        'base',
        'core',
    ];

    public function __construct(
        #[Alias(ServiceName::SERIALIZER)]
        private readonly SerializerInterface $serializer,
        private readonly ZipArchiveExtractor $zipArchiveExtractor,
        #[ReadPathFromConfig(ConfigKey::MAIN, ConfigKey::DIRECTORIES, ConfigKey::DIRECTORY_FACTORIO_FULL)]
        private readonly string $fullFactorioDirectory,
        #[ReadPathFromConfig(ConfigKey::MAIN, ConfigKey::DIRECTORIES, ConfigKey::DIRECTORY_MODS)]
        private readonly string $modsDirectory
    ) {
    }

    /**
     * Adds the archive of a mod to the local files.
     * @param string $modName
     * @param string $archiveFilePath
     * @throws ExportException
     */
    public function addModArchive(string $modName, string $archiveFilePath): void
    {
        if ($this->isVanillaMod($modName)) {
            throw new InternalException(sprintf('Trying to overwrite vanilla mod "%s"', $modName));
        }

        $this->zipArchiveExtractor->extract($archiveFilePath, $this->getLocalDirectory($modName));
    }

    /**
     * Returns the meta info from the mod.
     * @param string $modName
     * @return InfoJson
     * @throws ExportException
     */
    public function getInfo(string $modName): InfoJson
    {
        $contents = $this->readFile($modName, self::FILENAME_INFO);

        $encoding = (string) mb_detect_encoding($contents, 'UTF-8, ISO-8859-1', true);
        $contents = mb_convert_encoding($contents, 'UTF-8', $encoding);

        try {
            /** @var InfoJson $result */
            $result = $this->serializer->deserialize($contents, InfoJson::class, 'json');
            return $result;
        } catch (Exception $e) {
            throw new InvalidInfoJsonFileException($modName, $e);
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
        if ($this->isVanillaMod($modName)) {
            return $this->fullFactorioDirectory . '/data/' . $modName;
        }
        return $this->modsDirectory . '/' . $modName;
    }

    /**
     * Checks whether the specified mod name is from the vanilla game.
     * @param string $modName
     * @return bool
     */
    public function isVanillaMod(string $modName): bool
    {
        return in_array($modName, self::VANILLA_MODS, true);
    }
}
