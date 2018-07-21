<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Cache;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * The abstract class of the caches.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
abstract class AbstractCache
{
    /**
     * The directory to use as cache.
     * @var string
     */
    protected $cacheDirectory;

    /**
     * Initializes the cache.
     * @param string $cacheDirectory
     */
    public function __construct(string $cacheDirectory)
    {
        $this->cacheDirectory = $cacheDirectory;

        if (!is_dir($this->cacheDirectory)) {
            mkdir($this->cacheDirectory, 0777, true);
        }
    }

    /**
     * Returns the full file path of the specified filename.
     * @param string $fileName
     * @return string
     */
    protected function getFullFilePath(string $fileName): string
    {
        return $this->cacheDirectory . DIRECTORY_SEPARATOR . $fileName;
    }

    /**
     * Clears all files in the cache.
     * @return $this
     */
    public function clear()
    {
        /* @var SplFileInfo[] $files */
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->cacheDirectory, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $file) {
            if ($file->isDir() && !$file->isLink()) {
                rmdir($file->getPathname());
            } else {
                unlink($file->getPathname());
            }
        }
        return $this;
    }
}
