<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Output;

use Symfony\Component\Console\Output\ConsoleSectionOutput;
use Symfony\Component\Console\Terminal;

/**
 * The output of a process, limited to a constant number of lines.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ProcessOutput
{
    private const NUMBER_OF_LINES = 32;

    private ConsoleSectionOutput $output;
    private Terminal $terminal;

    /** @var array<string> */
    private array $lines = [];

    public function __construct(ConsoleSectionOutput $output)
    {
        $this->output = $output;
        $this->terminal = new Terminal();
    }

    public function addLine(string $line): self
    {
        $height = min($this->terminal->getHeight() - 6, self::NUMBER_OF_LINES);
        /** @var positive-int $width */
        $width = $this->terminal->getWidth();

        $this->lines = array_slice(array_merge($this->lines, str_split($line, $width)), -$height);
        $this->output->overwrite([
            ' Process output:',
            str_pad('', $width, '-'),
            ...$this->lines,
            str_pad('', $width, '-'),
        ]);
        return $this;
    }
}
