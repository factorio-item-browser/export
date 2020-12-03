<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\OutputProcessor;

use FactorioItemBrowser\Export\Console\ProcessOutput;
use FactorioItemBrowser\Export\Entity\Dump\Dump;

/**
 * The processor printing the output of the process to the console.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ConsoleOutputProcessor implements OutputProcessorInterface
{
    private const IGNORED_PREFIX = '>DUMP>';

    private ProcessOutput $processOutput;

    public function __construct(ProcessOutput $processOutput)
    {
        $this->processOutput = $processOutput;
    }

    public function processLine(string $outputLine, Dump $dump): void
    {
        if (strpos($outputLine, self::IGNORED_PREFIX) === false) {
            $this->processOutput->addLine($outputLine);
        }
    }

    public function processExitCode(int $exitCode, Dump $dump): void
    {
    }
}