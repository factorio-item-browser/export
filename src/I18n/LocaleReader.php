<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\I18n;

use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Mod\ModFileManager;

/**
 * The class reading the locales from the mod files.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class LocaleReader
{
    /**
     * The glob pattern to find all locale files of a mod.
     */
    protected const GLOB_PATTERN = 'locale/**/*.cfg';

    /**
     * The regular expression used for finding locale files.
     */
    protected const REGEXP_LOCALE_FILE = '#^locale/(.*)/#';

    /**
     * The regular expression used to detect actual translations.
     */
    protected const REGEXP_LOCALE = '#^(.*)=(.*)$#';

    /**
     * The regular expression used to detect a section.
     */
    protected const REGEXP_SECTION = '#^\[(.*)\]$#';

    /**
     * The mod file manager.
     * @var ModFileManager
     */
    protected $modFileManager;

    /**
     * Initializes the reader.
     * @param ModFileManager $modFileManager
     */
    public function __construct(ModFileManager $modFileManager)
    {
        $this->modFileManager = $modFileManager;
    }

    /**
     * Reads the locales of the specified mod.
     * @param string $modName
     * @return array
     * @throws ExportException
     */
    public function read(string $modName): array
    {
        $result = [];
        $localeFileNames = $this->getLocaleFileNames($modName);
        foreach ($localeFileNames as $locale => $fileNames) {
            $translations = [];
            foreach ($fileNames as $fileName) {
                $translations = array_merge($translations, $this->readLocaleFile($modName, $fileName));
            }
            $result[$locale] = $translations;
        }
        return $result;
    }

    /**
     * Returns all locale file names of the specified mod.
     * @param string $modName
     * @return array|string[][]
     */
    protected function getLocaleFileNames(string $modName): array
    {
        $result = [];
        foreach ($this->modFileManager->findFiles($modName, self::GLOB_PATTERN) as $fileName) {
            if (preg_match(self::REGEXP_LOCALE_FILE, $fileName, $match) > 0) {
                $locale = $match[1];
                if (!isset($result[$locale])) {
                    $result[$locale] = [];
                }
                $result[$locale][] = $fileName;
            }
        }
        return $result;
    }

    /**
     * Reads a locale file from the specified mod.
     * @param string $modName
     * @param string $fileName
     * @return array
     * @throws ExportException
     */
    protected function readLocaleFile(string $modName, string $fileName): array
    {
        return $this->parseLocaleFile($this->modFileManager->readFile($modName, $fileName));
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
            $line = trim($line);
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
