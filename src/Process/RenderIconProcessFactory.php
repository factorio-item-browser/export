<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Process;

use BluePsyduck\LaminasAutoWireFactory\Attribute\Alias;
use BluePsyduck\LaminasAutoWireFactory\Attribute\ReadConfig;
use FactorioItemBrowser\Export\AutoWire\Attribute\ReadPathFromConfig;
use FactorioItemBrowser\Export\Constant\ConfigKey;
use FactorioItemBrowser\Export\Constant\ServiceName;
use FactorioItemBrowser\ExportData\Entity\Icon;
use JMS\Serializer\SerializerInterface;

/**
 * The factory of the RenderIconProcess class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class RenderIconProcessFactory
{
    public function __construct(
        #[Alias(ServiceName::SERIALIZER)]
        private readonly SerializerInterface $serializer,
        #[ReadPathFromConfig(ConfigKey::MAIN, ConfigKey::DIRECTORIES, ConfigKey::DIRECTORY_FACTORIO_FULL)]
        private readonly string $fullFactorioDirectory,
        #[ReadPathFromConfig(ConfigKey::MAIN, ConfigKey::DIRECTORIES, ConfigKey::DIRECTORY_MODS)]
        private readonly string $modsDirectory,
        #[ReadPathFromConfig(ConfigKey::MAIN, ConfigKey::RENDER_ICON_BINARY)]
        private readonly string $renderIconBinary
    ) {
    }

    /**
     * Creates a render process for the specified icon.
     * @param Icon $icon
     * @return RenderIconProcess<string>
     */
    public function create(Icon $icon): RenderIconProcess
    {
        $command = [
            $this->renderIconBinary,
            $this->serializer->serialize($icon, 'json'),
        ];
        $env = [
            'FACTORIO_DATA_DIRECTORY' => $this->fullFactorioDirectory . '/data',
            'FACTORIO_MODS_DIRECTORY' => $this->modsDirectory,
        ];

        return new RenderIconProcess($icon, $command, $env);
    }
}
