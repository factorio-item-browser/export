<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\OutputProcessor;

use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Output\Console;
use FactorioItemBrowser\Export\Output\ProcessOutput;
use FactorioItemBrowser\Export\OutputProcessor\ConsoleOutputProcessor;
use FactorioItemBrowser\ExportData\ExportData;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the ConsoleOutputProcessor class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Export\OutputProcessor\ConsoleOutputProcessor
 */
class ConsoleOutputProcessorTest extends TestCase
{
    /**
     * @throws ExportException
     */
    public function test(): void
    {
        $exportData = $this->createMock(ExportData::class);

        $processOutput = $this->createMock(ProcessOutput::class);
        $processOutput->expects($this->exactly(2))
                      ->method('addLine')
                      ->withConsecutive(
                          ['abc'],
                          ['def']
                      );

        $console = $this->createMock(Console::class);
        $console->expects($this->once())
                ->method('createProcessOutput')
                ->willReturn($processOutput);

        $instance = new ConsoleOutputProcessor($console);
        $instance->processLine('abc', $exportData);
        $instance->processLine('>DUMP>fail', $exportData);
        $instance->processLine('def', $exportData);

        $instance->processExitCode(0, $exportData);
    }
}
