<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Exception;

use FactorioItemBrowser\Common\Constant\Constant;
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
     * The fallback message in case the base mod is missing.
     */
    protected const MESSAGE_BASE = 'The base mod is missing in the list. The base mod is always present. Why is the base mod missing?';

    /**
     * Initializes the exception.
     * @param string $modName
     * @param Throwable|null $previous
     */
    public function __construct(string $modName, ?Throwable $previous = null)
    {
        if ($modName === Constant::MOD_NAME_BASE) {
            $message = self::MESSAGE_BASE;
        } else {
            $message = sprintf(self::MESSAGE, $modName);
        }

        parent::__construct(sprintf(self::MESSAGE, $message), 0, $previous);
    }
}
