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
    /**
     * The mod to be downloaded.
     * @var Mod
     */
    protected $mod;

    /**
     * The release to be downloaded.
     * @var Release
     */
    protected $release;

    /**
     * Initializes the process.
     * @param Mod $mod
     * @param Release $release
     * @param string $downloadUrl
     * @param string $destinationFile
     */
    public function __construct(Mod $mod, Release $release, string $downloadUrl, string $destinationFile)
    {
        parent::__construct($downloadUrl, $destinationFile);

        $this->mod = $mod;
        $this->release = $release;
    }

    /**
     * Returns the mod to be downloaded.
     * @return Mod
     */
    public function getMod(): Mod
    {
        return $this->mod;
    }

    /**
     * Returns the release to be downloaded.
     * @return Release
     */
    public function getRelease(): Release
    {
        return $this->release;
    }
}
