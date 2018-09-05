<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\ModFile;

use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Utils\VersionUtils;
use FactorioItemBrowser\ExportData\Entity\Mod;
use ZipArchive;

/**
 * The reader of the actual mod file and its meta data.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ModFileReader
{
    /**
     * The mod file manager.
     * @var ModFileManager
     */
    protected $modFileManager;

    /**
     * Initializes the mod file reader.
     * @param ModFileManager $modFileManager
     */
    public function __construct(ModFileManager $modFileManager)
    {
        $this->modFileManager = $modFileManager;
    }

    /**
     * Calculates the checksum of the specified mod file.
     * @param string $fileName
     * @return string
     */
    public function calculateChecksum(string $fileName): string
    {
        return file_exists($fileName) ? (string) md5_file($fileName) : '';
    }

    /**
     * Reads the mod information from the specified file.
     * @param string $fileName
     * @param string $checksum
     * @return Mod
     * @throws ExportException
     */
    public function read(string $fileName, string $checksum): Mod
    {
        $mod = $this->createEntity($fileName, $checksum);
        $mod->setDirectoryName($this->detectDirectoryName($fileName));

        $this->parseInfoJson($mod);
        return $mod;
    }

    /**
     * Creates a new mod entity.
     * @param string $fileName
     * @param string $checksum
     * @return Mod
     */
    protected function createEntity(string $fileName, string $checksum): Mod
    {
        $result = new Mod();
        $result->setFileName(basename($fileName))
               ->setChecksum($checksum);
        return $result;
    }

    /**
     * Detects the directory within the mod file.
     * @param string $fileName
     * @return string
     * @throws ExportException
     */
    protected function detectDirectoryName(string $fileName): string
    {
        $zipArchive = new ZipArchive();
        $success = $zipArchive->open($fileName);
        if ($success !== true) {
            throw new ExportException('Unable to open zip archive ' . basename($fileName));
        }

        $result = '';
        for ($i = 0; $i < $zipArchive->numFiles; ++$i) {
            $stats = $zipArchive->statIndex($i);
            if (substr($stats['name'], -10) === '/info.json') {
                $result = substr($stats['name'], 0, -10);
                break;
            }
        }

        if ($result === '') {
            throw new ExportException('Unable to locate info.json in mod ' . basename($fileName));
        }
        return $result;
    }

    /**
     * Parses the info json file of the specified mod.
     * @param Mod $mod
     * @throws ExportException
     */
    protected function parseInfoJson(Mod $mod): void
    {
        $infoJson = $this->modFileManager->getInfoJson($mod);

        $mod->setName($infoJson->getString('name'))
            ->setAuthor($infoJson->getString('author'))
            ->setVersion(VersionUtils::normalize($infoJson->getString('version')));

        $mod->getTitles()->setTranslation('en', $infoJson->getString('title'));
        $mod->getDescriptions()->setTranslation('en', $infoJson->getString('description'));
    }
}
