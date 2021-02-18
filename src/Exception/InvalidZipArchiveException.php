<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Exception;

use Throwable;

/**
 * The exception thrown when an invalid zip archive is encountered.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class InvalidZipArchiveException extends ExportException
{
    private const MESSAGE = 'The zip archive %s could not be processed: %s';

    public function __construct(string $fileName, string $errorMessage, ?Throwable $previous = null)
    {
        parent::__construct(sprintf(self::MESSAGE, basename($fileName), $errorMessage), 0, $previous);
    }
}
