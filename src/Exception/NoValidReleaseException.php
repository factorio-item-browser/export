<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Exception;

use Throwable;

/**
 * The exception thrown when no valid release can be detected of a mod.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class NoValidReleaseException extends ExportException
{
    /**
     * The message template of the exception.
     */
    protected const MESSAGE = 'Unable to detect a valid release for mod %s.';

    /**
     * Initializes the exception.
     * @param string $modName
     * @param Throwable|null $previous
     */
    public function __construct(string $modName, ?Throwable $previous = null)
    {
        parent::__construct(sprintf(self::MESSAGE, $modName), 0, $previous);
    }
}
