<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Process;

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
    private SerializerInterface $serializer;
    private string $fullFactorioDirectory;
    private string $modsDirectory;
    private string $renderIconBinary;

    public function __construct(
        SerializerInterface $exportDataSerializer,
        string $fullFactorioDirectory,
        string $modsDirectory,
        string $renderIconBinary
    ) {
        $this->serializer = $exportDataSerializer;
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
