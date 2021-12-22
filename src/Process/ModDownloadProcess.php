<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Process;

use BluePsyduck\FactorioModPortalClient\Entity\Mod;
use BluePsyduck\FactorioModPortalClient\Entity\Release;

/**
 * The process to download a mod release.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ModDownloadProcess extends DownloadProcess
{
    public function __construct(
        private readonly Mod $mod,
        private readonly Release $release,
        string $downloadUrl,
        string $destinationFile,
    ) {
        parent::__construct($downloadUrl, $destinationFile);
    }

    public function getMod(): Mod
    {
        return $this->mod;
    }

    public function getRelease(): Release
    {
        return $this->release;
    }
}
