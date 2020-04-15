<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Exception;

use Throwable;

/**
 * The exception thrown when uploading a file to the FTP server has failed.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class UploadFailedException extends ExportException
{
    /**
     * The message template of the exception.
     */
    protected const MESSAGE = 'Upload to the FTP server failed: %s';

    /**
     * Initializes the exception.
     * @param string $message
     * @param Throwable|null $previous
     */
    public function __construct(string $message, ?Throwable $previous = null)
    {
        parent::__construct(sprintf(self::MESSAGE, $message), 0, $previous);
    }
}
