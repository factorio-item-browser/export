<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Helper;

use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Exception\InvalidZipArchiveException;
use FactorioItemBrowser\Export\Exception\ZipExtractException;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use ZipArchive;

/**
 * The class helping with extracting a zip archive to a target directory.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ZipArchiveExtractor
{
    public function __construct(
        private readonly Filesystem $fileSystem
    ) {
    }

    /**
     * Extracts the specified ZIP archive to the target directory. If the target directory already exists, it will
     * be removed and re-created to empty it prior extracting the files.
     * @param string $zipArchiveFile
     * @param string $targetDirectory
     * @throws ExportException
     */
    public function extract(string $zipArchiveFile, string $targetDirectory): void
    {
        $zipArchive = new ZipArchive();
        $success = $zipArchive->open($zipArchiveFile);
        if ($success !== true || $zipArchive->numFiles === 0) {
            throw new InvalidZipArchiveException($zipArchiveFile, 'Unable to open zip file.');
        }

        try {
            $this->fileSystem->remove($targetDirectory);
            $this->fileSystem->mkdir($targetDirectory);

            for ($index = 0; $index < $zipArchive->numFiles; ++$index) {
                ['name' => $fileName] = $zipArchive->statIndex($index);
                if (
                    is_string($fileName)
                    && !str_ends_with($fileName, '/') // Ignore directories
                    && str_contains($fileName, '/') // Ignore top-level directory
                ) {
                    $targetFile = $targetDirectory . substr($fileName, intval(strpos($fileName, '/')));
                    $stream = $zipArchive->getStream($fileName);
                    if ($stream !== false) {
                        $this->fileSystem->dumpFile($targetFile, $stream);
                    }
                }
            }
        } catch (IOException $e) {
            throw new ZipExtractException($zipArchiveFile, $e->getMessage(), $e);
        } finally {
            $zipArchive->close();
        }
    }
}
