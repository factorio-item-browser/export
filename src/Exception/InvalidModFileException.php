<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Exception;

use Throwable;

/**
 * The exception thrown when an invalid mod file was encountered.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class InvalidModFileException extends ExportException
{
    /**
     * The message of the exception.
     */
    protected const MESSAGE = 'The downloaded file %s could not be processed: %s';

    /**
     * Initializes the exception.
     * @param string $fileName
     * @param string $errorMessage
     * @param Throwable|null $previous
     */
    public function __construct(string $fileName, string $errorMessage, ?Throwable $previous = null)
    {
        parent::__construct(sprintf(self::MESSAGE, basename($fileName), $errorMessage), 0, $previous);
    }
}
