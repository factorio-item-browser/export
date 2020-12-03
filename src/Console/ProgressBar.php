<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Console;

use Symfony\Component\Console\Helper\ProgressBar as SymfonyProgressBar;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\ConsoleSectionOutput;

/**
 * The class drawing a progress bar, including additional status information.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ProgressBar
{
    private SymfonyProgressBar $progressBar;
    private ConsoleSectionOutput $statusOutput;

    /** @var array<string, string> */
    private array $lines = [];

    public function __construct(ConsoleOutputInterface $output, string $label)
    {
        $this->progressBar = new SymfonyProgressBar($output->section());
        $this->progressBar->setFormat(" {$label}: %current%/%max% [%bar%] %percent:3s%%");
        $this->statusOutput = $output->section();
    }

    public function setNumberOfSteps(int $numberOfSteps): self
    {
        $this->progressBar->setMaxSteps($numberOfSteps);
        $this->progressBar->display();
        return $this;
    }

    public function getNumberOfSteps(): int
    {
        return $this->progressBar->getMaxSteps();
    }

    /**
     * Starts the item with the specified key, displaying it with the specified label.
     * @param string $key
     * @param string $label
     * @return $this
     */
    public function start(string $key, string $label): self
    {
        $this->lines[$key] = ' ' . $label;
        $this->statusOutput->overwrite($this->lines);
        return $this;
    }

    /**
     * Updates the item with the specified key, setting a new label for it.
     * @param string $key
     * @param string $label
     * @return $this
     */
    public function update(string $key, string $label): self
    {
        return $this->start($key, $label);
    }

    /**
     * Finishes the item with the specified key The progress bar will be advanced one step.
     * @param string $key
     * @return $this
     */
    public function finish(string $key): self
    {
        unset($this->lines[$key]);
        $this->progressBar->advance();
        $this->statusOutput->overwrite($this->lines);
        return $this;
    }
}
