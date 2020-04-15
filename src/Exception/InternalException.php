<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Exception;

use Throwable;

/**
 * The exception thrown when an internal error occurred, i.e. something which should not happen at all.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class InternalException extends ExportException
{
    /**
     * The message of the exception.
     */
    protected const MESSAGE = 'Internal server error: %s';

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
