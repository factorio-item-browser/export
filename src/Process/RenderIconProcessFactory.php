<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Process;

use BluePsyduck\LaminasAutoWireFactory\Attribute\Alias;
use BluePsyduck\LaminasAutoWireFactory\Attribute\ReadConfig;
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
    private readonly string $fullFactorioDirectory;
    private readonly string $modsDirectory;
    private readonly string $renderIconBinary;

    public function __construct(
        #[Alias(ServiceName::SERIALIZER)]
        private readonly SerializerInterface $serializer,
        #[ReadConfig(ConfigKey::MAIN, ConfigKey::DIRECTORIES, ConfigKey::DIRECTORY_FACTORIO_FULL)]
        string $fullFactorioDirectory,
        #[ReadConfig(ConfigKey::MAIN, ConfigKey::DIRECTORIES, ConfigKey::DIRECTORY_MODS)]
        string $modsDirectory,
        #[ReadConfig(ConfigKey::MAIN, ConfigKey::RENDER_ICON_BINARY)]
        string $renderIconBinary
    ) {
        $this->fullFactorioDirectory = (string) realpath($fullFactorioDirectory);
        $this->modsDirectory = (string) realpath($modsDirectory);
        $this->renderIconBinary = (string) realpath($renderIconBinary);
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
