<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Process;

use BluePsyduck\FactorioModPortalClient\Client\Facade;
use BluePsyduck\FactorioModPortalClient\Entity\Mod;
use BluePsyduck\FactorioModPortalClient\Entity\Release;
use BluePsyduck\LaminasAutoWireFactory\Attribute\ReadConfig;
use FactorioItemBrowser\Export\AutoWire\Attribute\ReadPathFromConfig;
use FactorioItemBrowser\Export\Constant\ConfigKey;

/**
 * The factory for the mod download processes.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ModDownloadProcessFactory
{
    public function __construct(
        private readonly Facade $modPortalClientFacade,
        #[ReadPathFromConfig(ConfigKey::MAIN, ConfigKey::DIRECTORIES, ConfigKey::DIRECTORY_TEMP)]
        private readonly string $tempDirectory,
    ) {
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
