<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Exception;

use Throwable;

/**
 * The exception thrown when the info.json file could not be processed.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class InvalidInfoJsonFileException extends ExportException
{
    /**
     * The message template of the exception.
     */
    protected const MESSAGE = 'The info.json file of the mod %s was invalid and could not be read.';

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
