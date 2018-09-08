<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Mod;

use FactorioItemBrowser\Export\Cache\LocaleCache;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\ExportData\Entity\Mod;

/**
 * The class reading the locales from the mod files.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class LocaleReader
{
    /**
     * The regular expression used for finding locale files.
     */
    protected const REGEXP_LOCALE_FILE = '#^(locale/([a-zA-Z\-]+)/(.*)\.cfg)$#';

    /**
     * The regular expression used to detect actual translations.
     */
    protected const REGEXP_LOCALE = '#^(.*)=(.*)$#';

    /**
     * The regular expression used to detect a section.
     */
    protected const REGEXP_SECTION = '#^\[(.*)\]$#';

    /**
     * The locale cache.
     * @var LocaleCache
     */
    protected $localeCache;

    /**
     * The mod file manager.
     * @var ModFileManager
     */
    protected $modFileManager;

    /**
     * Initializes the reader.
     * @param LocaleCache $localeCache
     * @param ModFileManager $modFileManager
     */
    public function __construct(LocaleCache $localeCache, ModFileManager $modFileManager)
    {
        $this->localeCache = $localeCache;
        $this->modFileManager = $modFileManager;
    }

    /**
     * Reads the locales of the specified mod.
     * @param Mod $mod
     * @return array
     * @throws ExportException
     */
    public function read(Mod $mod): array
    {
        $result = $this->localeCache->read($mod->getName());
        if ($result === null) {
            $result = $this->readLocaleFiles($mod);
            $this->localeCache->write($mod->getName(), $result);
        }
        return $result;
    }

    /**
     * Reads the locale file from the specified mod.
     * @param Mod $mod
     * @return array
     * @throws ExportException
     */
    protected function readLocaleFiles(Mod $mod): array
    {
        $result = [];
        $localeFileNames = $this->getLocaleFileNames($mod);
        foreach ($localeFileNames as $locale => $fileNames) {
            $translations = [];
            foreach ($fileNames as $fileName) {
                $translations = array_merge($translations, $this->readLocaleFile($mod, $fileName));
            }
            $result[$locale] = $translations;
        }
        return $result;
    }

    /**
     * Reads a locale file from the specified mod.
     * @param Mod $mod
     * @param string $fileName
     * @return array
     */
    protected function readLocaleFile(Mod $mod, string $fileName): array
    {
        return $this->parseLocaleFile((string) $this->modFileManager->readFile($mod, $fileName));
    }

    /**
     * Returns all locale file names of the specified mod.
     * @param Mod $mod
     * @return array|string[][]
     * @throws ExportException
     */
    protected function getLocaleFileNames(Mod $mod): array
    {
        $result = [];
        foreach ($this->modFileManager->getAllFileNamesOfMod($mod) as $fileName) {
            if (preg_match(self::REGEXP_LOCALE_FILE, $fileName, $match) > 0) {
                $locale = $match[2];
                if (!isset($result[$locale])) {
                    $result[$locale] = [];
                }
                $result[$locale][] = $match[1];
            }
        }
        return $result;
    }

    /**
     * Parses the contents of the locale file.
     * @param string $content
     * @return array|string[]
     */
    protected function parseLocaleFile(string $content): array
    {
        $currentSection = '';
        $result = [];

        foreach (explode(PHP_EOL, $content) as $line) {
            if (preg_match(self::REGEXP_LOCALE, $line, $match) > 0) {
                $key = ltrim($currentSection . '.' . trim($match[1]), '.');
                $value = str_replace('\n', PHP_EOL, trim($match[2]));
                $result[$key] = $value;
            } elseif (preg_match(self::REGEXP_SECTION, $line, $match) > 0) {
                $currentSection = trim($match[1]);
            }
        }
        return $result;
    }
}
