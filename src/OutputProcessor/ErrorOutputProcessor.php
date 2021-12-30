<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\OutputProcessor;

use FactorioItemBrowser\Export\Exception\FactorioExecutionException;
use FactorioItemBrowser\ExportData\ExportData;

/**
 * The output processor catching the last error from the output.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ErrorOutputProcessor implements OutputProcessorInterface
{
    private const REGEX_ERROR = '(^[ ]*\d+\.\d{3} Error |^Error: )';

    /** @var array<string> */
    private array $errorLines = [];
    private bool $isCatchingError = false;

    public function processLine(string $outputLine, ExportData $exportData): void
    {
        if (preg_match(self::REGEX_ERROR, $outputLine) > 0) {
            $this->errorLines[] = $outputLine;
            $this->isCatchingError = true;
        } elseif ($this->isCatchingError) {
            $this->errorLines[] = $outputLine;
        }
    }

    public function processExitCode(int $exitCode, ExportData $exportData): void
    {
        if ($exitCode !== 0) {
            throw new FactorioExecutionException($exitCode, implode(PHP_EOL, $this->errorLines));
        }
    }
}
