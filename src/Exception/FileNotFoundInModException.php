<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Exception;

use Throwable;

/**
 * The exception thrown when an expected file was not found in a certain mod.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class FileNotFoundInModException extends ExportException
{
    /**
     * The message template of the exception.
     */
    protected const MESSAGE = 'File %s cannot be found in mod %s.';

    /**
     * Initializes the exception.
     * @param string $modName
     * @param string $fileName
     * @param Throwable|null $previous
     */
    public function __construct(string $modName, string $fileName, ?Throwable $previous = null)
    {
        parent::__construct(sprintf(self::MESSAGE, $fileName, $modName), 0, $previous);
    }
}
