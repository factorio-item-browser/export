<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Cache;

use FactorioItemBrowser\Export\Exception\ExportException;

/**
 * The cache of the locale files of the mods.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class LocaleCache extends AbstractCache
{
    /**
     * The filename of the actual translations to use.
     */
    protected const TRANSLATION_FILENAME = 'translation.php';

    /**
     * Reads the translations from the cache.
     * @param string $modName
     * @return array|null
     */
    public function read(string $modName): ?array
    {
        $filePath = $this->getFullFilePath($modName, self::TRANSLATION_FILENAME);
        $result = null;
        if (file_exists($filePath) && is_readable($filePath)) {
            $result = require($filePath);
        }
        return is_array($result) ? $result : null;
    }

    /**
     * Writes the translations to the cache.
     * @param string $modName
     * @param array $translations
     * @throws ExportException
     */
    public function write(string $modName, array $translations): void
    {
        $filePath = $this->getFullFilePath($modName, self::TRANSLATION_FILENAME);
        $contents = '<?php return ' . var_export($translations, true) . ';';
        $this->writeFile($filePath, $contents);
    }
}
