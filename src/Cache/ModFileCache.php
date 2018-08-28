<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Cache;

use FactorioItemBrowser\Export\Exception\ExportException;

/**
 * The cache of the files of mods.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ModFileCache extends AbstractCache
{
    /**
     * Reads the translations from the cache.
     * @param string $modName
     * @param string $fileName
     * @return string|null
     */
    public function read(string $modName, string $fileName): ?string
    {
        return $this->readFile($this->getFullFilePath($modName, $fileName));
    }

    /**
     * Writes the translations to the cache.
     * @param string $modName
     * @param string $fileName
     * @param string $content
     * @throws ExportException
     */
    public function write(string $modName, string $fileName, string $content): void
    {
        $this->writeFile($this->getFullFilePath($modName, $fileName), $content);
        return;
    }
}
