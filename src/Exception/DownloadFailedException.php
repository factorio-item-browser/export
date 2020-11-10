<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Exception;

use BluePsyduck\FactorioModPortalClient\Entity\Mod;
use BluePsyduck\FactorioModPortalClient\Entity\Release;
use Throwable;

/**
 * The exception thrown when a mod download failed.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class DownloadFailedException extends ExportException
{
    /**
     * The message template of the exception.
     */
    protected const MESSAGE = 'Download of mod %s (%s) failed: %s';

    /**
     * Initializes the exception.
     * @param Mod $mod
     * @param Release $release
     * @param string $message
     * @param Throwable|null $previous
     */
    public function __construct(Mod $mod, Release $release, string $message, ?Throwable $previous = null)
    {
        parent::__construct(sprintf(
            self::MESSAGE,
            $mod->getName(),
            (string) $release->getVersion(),
            $message,
        ), 0, $previous);
    }
}
