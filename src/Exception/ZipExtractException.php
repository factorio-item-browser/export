<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Exception;

use Throwable;

/**
 * The exception thrown when a file could not be extracted from a zip archive.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ZipExtractException extends ExportException
{
    private const MESSAGE = 'Failed to extract files from zip archive %s: %s';

    public function __construct(string $zipFileName, string $errorMessage, ?Throwable $previous = null)
    {
        parent::__construct(sprintf(self::MESSAGE, basename($zipFileName), $errorMessage), 0, $previous);
    }
}
