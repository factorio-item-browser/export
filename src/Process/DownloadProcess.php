<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Process;

use BluePsyduck\FactorioModPortalClient\Entity\Mod;
use BluePsyduck\FactorioModPortalClient\Entity\Release;
use Symfony\Component\Process\Process;

/**
 * The process to download a file from the web.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class DownloadProcess extends Process
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
     * The full download URL.
     * @var string
     */
    protected $downloadUrl;

    /**
     * The destination of the downloaded file.
     * @var string
     */
    protected $destinationFile;

    /**
     * Initializes the process.
     * @param Mod $mod
     * @param Release $release
     * @param string $downloadUrl
     * @param string $destinationFile
     */
    public function __construct(Mod $mod, Release $release, string $downloadUrl, string $destinationFile)
    {
        parent::__construct([
            'wget',
            '-o',
            '/dev/null',
            '-O',
            $destinationFile,
            $downloadUrl,
        ]);

        $this->mod = $mod;
        $this->release = $release;
        $this->downloadUrl = $downloadUrl;
        $this->destinationFile = $destinationFile;

        $this->setTimeout(null);
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

    /**
     * Returns the full download URL.
     * @return string
     */
    public function getDownloadUrl(): string
    {
        return $this->downloadUrl;
    }

    /**
     * Returns the destination of the downloaded file.
     * @return string
     */
    public function getDestinationFile(): string
    {
        return $this->destinationFile;
    }
}
