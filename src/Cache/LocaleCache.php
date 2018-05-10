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
     * Returns whether the specified mod has translations in the cache.
     * @param string $modName
     * @return bool
     */
    public function has(string $modName): bool
    {
        return file_exists($this->getCacheFileName($modName));
    }

    /**
     * Reads the translations from the cache.
     * @param string $modName
     * @return array
     */
    public function read(string $modName): array
    {
        $result = [];
        if ($this->has($modName)) {
            $result = require ($this->getCacheFileName($modName));
        }
        return $result;
    }

    /**
     * Writes the translations to the cache.
     * @param string $modName
     * @param array $translations
     * @return $this
     * @throws ExportException
     */
    public function write(string $modName, array $translations)
    {
        $filePath = $this->getCacheFileName($modName);
        $success = file_put_contents($filePath, '<?php return ' . var_export($translations, true) . ';');
        if (!$success) {
            throw new ExportException('Unable to cache locale data into file ' . $filePath);
        }
        return $this;
    }

    /**
     * Returns the full file name of the cache file to use.
     * @param string $modName
     * @return string
     */
    protected function getCacheFileName(string $modName): string
    {
        return $this->getFullFilePath($modName . '.php');
    }
}