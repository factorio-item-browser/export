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
    /**
     * The serializer.
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * The path to the factorio directory.
     * @var string
     */
    protected $factorioDirectory;

    /**
     * The path to the mods directory.
     * @var string
     */
    protected $modsDirectory;

    /**
     * The path to the render-icon binary.
     * @var string
     */
    protected $renderIconBinary;

    /**
     * Initializes the factory.
     * @param SerializerInterface $exportDataSerializer
     * @param string $factorioDirectory
     * @param string $modsDirectory
     * @param string $renderIconBinary
     */
    public function __construct(
        SerializerInterface $exportDataSerializer,
        string $factorioDirectory,
        string $modsDirectory,
        string $renderIconBinary
    ) {
        $this->serializer = $exportDataSerializer;
        $this->factorioDirectory = $factorioDirectory;
        $this->modsDirectory = $modsDirectory;
        $this->renderIconBinary = $renderIconBinary;
    }

    /**
     * Creates a render process for the specified icon.
     * @param Icon $icon
     * @return RenderIconProcess<string>
     */
    public function create(Icon $icon): RenderIconProcess
    {
        $command = [
            (string) realpath($this->renderIconBinary),
            $this->serializer->serialize($icon, 'json'),
        ];
        $env = [
            'FACTORIO_DATA_DIRECTORY' => (string) realpath($this->factorioDirectory . '/data'),
            'FACTORIO_MODS_DIRECTORY' => (string) realpath($this->modsDirectory),
        ];

        return new RenderIconProcess($icon, $command, $env);
    }
}