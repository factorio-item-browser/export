<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Cache;

use FactorioItemBrowser\Export\Exception\ExportException;
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
    }

    /**
     * Ensures the specified directory to be present and writable.
     * @param string $directory
     * @throws ExportException
     */
    protected function ensureDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            $success = mkdir($directory, 0775, true);
            if (!$success) {
                throw new ExportException('Unable to create directory ' . $directory . '.');
            }
        }
        if (!is_writable($directory)) {
            throw new ExportException('Directory ' . $directory . ' is not writable.');
        }
    }

    /**
     * Returns the full file path of the specified filename.
     * @param string $modName
     * @param string $fileName
     * @return string
     */
    protected function getFullFilePath(string $modName, string $fileName): string
    {
        return implode(DIRECTORY_SEPARATOR, [
            $this->cacheDirectory,
            $modName,
            $fileName
        ]);
    }

    /**
     * Writes content into the cache file.
     * @param string $filePath
     * @param string $content
     * @throws ExportException
     */
    protected function writeFile(string $filePath, string $content): void
    {
        $this->ensureDirectory(dirname($filePath));

        $success = false;
        if (!file_exists($filePath) || is_writable($filePath)) {
            $success = file_put_contents($filePath, $content);
        }
        if ($success === false) {
            throw new ExportException('Unable to write cache file ' . $filePath);
        }
    }

    /**
     * Reads the content of the specified cache file.
     * @param string $filePath
     * @return string|null
     */
    protected function readFile(string $filePath): ?string
    {
        $result = null;
        if (file_exists($filePath) && is_readable($filePath)) {
            $result = file_get_contents($filePath);
        }
        return is_string($result) ? $result : null;
    }

    /**
     * Clears all files in the cache.
     */
    public function clear(): void
    {
        $this->clearDirectory($this->cacheDirectory);
    }

    /**
     * Clears the specified mod from the cache.
     * @param string $modName
     */
    public function clearMod(string $modName): void
    {
        $directory = implode(DIRECTORY_SEPARATOR, [
            $this->cacheDirectory,
            $modName
        ]);
        $this->clearDirectory($directory);
    }

    /**
     * Clears the specified directory from the cache.
     * @param string $directory
     */
    protected function clearDirectory(string $directory): void
    {
        if (is_dir($directory)) {
            /* @var SplFileInfo[] $files */
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($files as $file) {
                if ($file->isDir() && !$file->isLink()) {
                    rmdir($file->getPathname());
                } else {
                    unlink($file->getPathname());
                }
            }
        }
    }
}
