<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\OutputProcessor;

use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\Exception\ExportException;

/**
 * The interface of the output processors.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
interface OutputProcessorInterface
{
    /**
     * Processes a line from the output of the Factorio process.
     * @param string $outputLine
     * @param Dump $dump
     * @throws ExportException
     */
    public function processLine(string $outputLine, Dump $dump): void;

    /**
     * Processes the exit code of the Factorio process.
     * @param int $exitCode
     * @param Dump $dump
     * @throws ExportException
     */
    public function processExitCode(int $exitCode, Dump $dump): void;
}
