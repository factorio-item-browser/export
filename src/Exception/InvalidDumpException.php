<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Exception;

use Throwable;

/**
 * The exception thrown when an invalid dump has been encountered.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class InvalidDumpException extends ExportException
{
    /**
     * The message template of the exception.
     */
    protected const MESSAGE = 'Failed to process dumped data of %s stage: %s';

    /**
     * Initializes the exception.
     * @param string $stage
     * @param string $errorMessage
     * @param Throwable|null $previous
     */
    public function __construct(string $stage, string $errorMessage, ?Throwable $previous = null)
    {
        parent::__construct(sprintf(self::MESSAGE, $stage, $errorMessage), 0, $previous);
    }
}
