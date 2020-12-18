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
class MissingModsException extends ExportException
{
    private const MESSAGE = 'Mods %s cannot be found on the Mod Portal.';

    /**
     * @param array<string> $modNames
     * @param Throwable|null $previous
     */
    public function __construct(array $modNames, ?Throwable $previous = null)
    {
        parent::__construct(sprintf(self::MESSAGE, implode(', ', $modNames)), 0, $previous);
    }
}
