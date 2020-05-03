<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Exception;

use Throwable;

/**
 * The exception thrown when the dump mod was actually not loaded.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class DumpModNotLoadedException extends ExportException
{
    /**
     * The message of the exception.
     */
    protected const MESSAGE = 'The dump mod was not loaded as last mod. This is an indication that at least one mod '
        . 'is not compatible with the used version of Factorio.';

    /**
     * Initializes the exception.
     * @param Throwable|null $previous
     */
    public function __construct(?Throwable $previous = null)
    {
        parent::__construct(self::MESSAGE, 0, $previous);
    }
}
