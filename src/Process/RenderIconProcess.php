<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Process;

use FactorioItemBrowser\ExportData\Entity\Icon;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Process\Process;

/**
 * The process for rendering a single icon.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class RenderIconProcess extends Process
{
    /**
     * The icon.
     * @var Icon
     */
    protected $icon;

    /**
     * Initializes the process.
     * @param SerializerInterface $exportDataSerializer
     * @param Icon $icon
     */
    public function __construct(SerializerInterface $exportDataSerializer, Icon $icon)
    {
        $this->icon = $icon;

        parent::__construct($this->buildCommand($exportDataSerializer, $icon));

        $this->setEnv(['SUBCMD' => 1]);
        $this->setTimeout(null);
    }

    /**
     * Builds the command to actually call.
     * @param SerializerInterface $serializer
     * @param Icon $icon
     * @return array|string[]
     */
    protected function buildCommand(SerializerInterface $serializer, Icon $icon): array
    {
        return [
            'php',
            $_SERVER['SCRIPT_FILENAME'],
            'render-icon',
            $serializer->serialize($icon, 'json'),
        ];
    }

    /**
     * Returns the icon of the process.
     * @return Icon
     */
    public function getIcon(): Icon
    {
        return $this->icon;
    }
}
