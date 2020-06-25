<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Exception;

use Throwable;

/**
 * The exception thrown when a requested mod cannot be found on the mod portal.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class MissingModException extends ExportException
{
    /**
     * The message of the exception.
     */
    protected const MESSAGE = 'Mod %s cannot be found on the Mod Portal.';

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
