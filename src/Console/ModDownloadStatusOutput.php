<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Console;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleOutputInterface;

/**
 * The output of the status during the download of mods.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ModDownloadStatusOutput
{
    private Table $table;
    private ProgressBar $progressBar;

    public function __construct(ConsoleOutputInterface $consoleOutput)
    {
        $this->table = new Table($consoleOutput->section());
        $this->table->setColumnWidths([0, 26, 11]);
        $this->progressBar = new ProgressBar($consoleOutput, 'Mods');
    }

    public function addMod(string $modName, string $currentVersion, ?string $requestedVersion = null): self
    {
        if ($requestedVersion !== null) {
            $version = sprintf(
                '%s -> %s',
                str_pad($currentVersion, 11, ' ', STR_PAD_LEFT),
                str_pad($requestedVersion, 11, ' ', STR_PAD_RIGHT),
            );
            $status = '<fg=yellow>new version</>';

            $this->progressBar->setNumberOfSteps($this->progressBar->getNumberOfSteps() + 1);
        } else {
            $version = str_pad($currentVersion, 11, ' ', STR_PAD_LEFT);
            $status = '<fg=green>up-to-date</>';
        }

        $this->table->addRow([$modName, $version, $status]);
        return $this;
    }

    public function render(): self
    {
        $this->table->render();
        return $this;
    }

    public function startDownloading(string $modName): self
    {
        $this->progressBar->start($modName, "<fg=yellow>Downloading</> {$modName}");
        return $this;
    }

    public function startExtracting(string $modName): self
    {
        $this->progressBar->update($modName, "<fg=blue>Extracting</> {$modName}");
        return $this;
    }

    public function finish(string $modName): self
    {
        $this->progressBar->finish($modName);
        return $this;
    }
}
