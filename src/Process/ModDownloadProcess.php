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
    private Mod $mod;
    private Release $release;

    public function __construct(Mod $mod, Release $release, string $downloadUrl, string $destinationFile)
    {
        parent::__construct($downloadUrl, $destinationFile);

        $this->mod = $mod;
        $this->release = $release;
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
