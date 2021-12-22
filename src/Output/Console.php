<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Output;

use BluePsyduck\LaminasAutoWireFactory\Attribute\ReadConfig;
use Exception;
use Generator;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\ConsoleSectionOutput;
use Symfony\Component\Console\Terminal;

/**
 * The wrapper class for the actual console.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class Console
{
    private readonly Terminal $terminal;

    public function __construct(
        private readonly ConsoleOutputInterface $output,
        #[ReadConfig('debug')]
        private readonly bool $isDebug
    ) {
        $this->terminal = new Terminal();
    }

    /**
     * Writes a headline with the specified message.
     * @param string $message
     * @return $this
     */
    public function writeHeadline(string $message): self
    {
        $this->output->writeln(<<<EOT
            
            <bg=cyan;fg=black;options=bold>{$this->createHorizontalLine(' ')}
             {$message}
            </>
            EOT);
        return $this;
    }

    /**
     * Writes a step to the console.
     * @param string $step
     * @return $this
     */
    public function writeStep(string $step): self
    {
        $this->output->writeln(<<<EOT
            
            <fg=cyan;options=bold> {$step}
            {$this->createHorizontalLine('-')}</>
            EOT);
        return $this;
    }

    /**
     * Writes an action to the console.
     * @param string $action
     * @return $this
     */
    public function writeAction(string $action): self
    {
        $this->output->writeln('> ' . $action . '...');
        return $this;
    }

    /**
     * Writes a simple message, like a comment, to the console.
     * @param string $message
     * @return $this
     */
    public function writeMessage(string $message): self
    {
        $this->output->writeln('# ' . $message);
        return $this;
    }

    /**
     * Writes an exception to the console.
     * @param Exception $e
     * @return $this
     */
    public function writeException(Exception $e): self
    {
        $exceptionName = substr((string) strrchr(get_class($e), '\\'), 1);
        $this->output->writeln(<<<EOT
            
            <bg=red;fg=white;options=bold>{$this->createHorizontalLine(' ')}
             {$exceptionName}: {$e->getMessage()}
            </>
            EOT);

        if ($this->isDebug) {
            $this->output->writeln("<fg=red>{$e->getTraceAsString()}</>");
        }
        return $this;
    }

    /**
     * Creates a horizontal line of the specified character.
     * @param string $character
     * @return string
     */
    protected function createHorizontalLine(string $character): string
    {
        return str_pad('', $this->terminal->getWidth(), $character);
    }

    /**
     * Creates the output for the list of mods with their versions.
     * @return ModListOutput
     */
    public function createModListOutput(): ModListOutput
    {
        return new ModListOutput($this->output->section());
    }

    /**
     * Creates the output of a process.
     * @return ProcessOutput
     */
    public function createProcessOutput(): ProcessOutput
    {
        return new ProcessOutput($this->output->section());
    }

    /**
     * Creates a progress bar, using the specified label.
     * @param string $label
     * @return ProgressBar
     */
    public function createProgressBar(string $label): ProgressBar
    {
        return new ProgressBar($this->output, $label);
    }

    /**
     * Iterates through a list of items, displaying a progress bar for them, showing the progress of the iteration.
     * @template TKey
     * @template TValue
     * @param string $label
     * @param iterable<TKey, TValue> $iterable
     * @return Generator<TKey, TValue>
     */
    public function iterateWithProgressbar(string $label, iterable $iterable): Generator
    {
        $progressBar = $this->createProgressBar($label);
        if (is_countable($iterable)) {
            $progressBar->setNumberOfSteps(count($iterable));
        }

        foreach ($iterable as $key => $value) {
            yield $key => $value;
            $progressBar->finish((string) $key);
        }
    }

    /**
     * Creates a section in the console for additional output.
     * @return ConsoleSectionOutput
     */
    public function createSection(): ConsoleSectionOutput
    {
        return $this->output->section();
    }
}
