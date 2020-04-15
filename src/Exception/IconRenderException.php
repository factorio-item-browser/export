<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Exception;

use FactorioItemBrowser\ExportData\Entity\Icon;
use Throwable;

/**
 * The exception thrown when rendering an icon has failed.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class IconRenderException extends ExportException
{
    /**
     * The message template of the exception.
     */
    protected const MESSAGE = 'Failed to render icon %s: %s';

    /**
     * Initializes the exception.
     * @param Icon $icon
     * @param string $message
     * @param Throwable|null $previous
     */
    public function __construct(Icon $icon, string $message, ?Throwable $previous = null)
    {
        parent::__construct(sprintf(self::MESSAGE, $icon->getId(), $message), 0, $previous);
    }
}
