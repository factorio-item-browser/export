<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Process;

use FactorioItemBrowser\ExportData\Entity\Icon;
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
     * @param array<string> $command
     * @param array<string, string> $env
     */
    public function __construct(
        private readonly Icon $icon,
        array $command,
        array $env,
    ) {
        parent::__construct($command, null, $env, null, null);
    }

    public function getIcon(): Icon
    {
        return $this->icon;
    }
}
