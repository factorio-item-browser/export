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

        parent::__construct([
            'php',
            $_SERVER['SCRIPT_FILENAME'],
            'render-icon',
            $exportDataSerializer->serialize($icon, 'json'),
        ]);

        $this->setEnv(['SUBCMD' => 1]);
        $this->setTimeout(null);
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
