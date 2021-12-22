<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Output;

use BluePsyduck\FactorioModPortalClient\Entity\Version;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The output to print the list of mods with their versions.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ModListOutput
{
    /** @var array<string, array<string>> */
    private array $mods = [];

    public function __construct(
        private readonly OutputInterface $output
    ) {
    }

    public function add(string $modName, ?Version $currentVersion = null, ?Version $requestedVersion = null): self
    {
        $this->mods[$modName] = [
            $modName,
            (string) $currentVersion,
            (string) $requestedVersion,
            $requestedVersion === null ? '<fg=green>up-to-date</>' : '<fg=yellow>new version</>',
        ];

        return $this;
    }

    public function render(): self
    {
        ksort($this->mods);

        $table = new Table($this->output);
        $table->setStyle('compact');
        $table->addRows($this->mods);
        $table->render();

        return $this;
    }
}
