<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Exception;

use Throwable;

/**
 * The exception thrown when the execution of Factorio failed.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class FactorioExecutionException extends ExportException
{
    private const MESSAGE = "Factorio exited with code %d:\n%s";

    public function __construct(int $exitCode, string $errorMessage, ?Throwable $previous = null)
    {
        parent::__construct(sprintf(self::MESSAGE, $exitCode, $errorMessage), $exitCode, $previous);
    }
}
