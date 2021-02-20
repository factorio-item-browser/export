<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Process;

use BluePsyduck\FactorioModPortalClient\Client\Facade;
use BluePsyduck\FactorioModPortalClient\Entity\Mod;
use BluePsyduck\FactorioModPortalClient\Entity\Release;

/**
 * The factory for the mod download processes.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ModDownloadProcessFactory
{
    private Facade $modPortalClientFacade;
    private string $tempDirectory;

    public function __construct(Facade $modPortalClientFacade, string $tempDirectory)
    {
        $this->modPortalClientFacade = $modPortalClientFacade;
        $this->tempDirectory = (string) realpath($tempDirectory);
    }

    /**
     * @param Mod $mod
     * @param Release $release
     * @return ModDownloadProcess<string>
     */
    public function create(Mod $mod, Release $release): ModDownloadProcess
    {
        return new ModDownloadProcess(
            $mod,
            $release,
            $this->modPortalClientFacade->getDownloadUrl($release->getDownloadUrl()),
            $this->tempDirectory . '/' . $release->getFileName()
        );
    }
}
