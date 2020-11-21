<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\OutputProcessor;

use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\Exception\DumpModNotLoadedException;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\OutputProcessor\ModNameOutputProcessor;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the ModNameOutputProcessor class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\OutputProcessor\ModNameOutputProcessor
 */
class ModNameOutputProcessorTest extends TestCase
{
    /**
     * @return array<mixed>
     */
    public function provideProcessLine(): array
    {
        return [
            ['  69.420 Checksum of abc: 1337', ['def'], ['def', 'abc']],
            ['   0.061 Loading mod base 0.18.21 (data.lua)', ['def'], ['def']],
        ];
    }

    /**
     * @param string $outputLine
     * @param array<string> $modNames
     * @param array<string> $expectedModNames
     * @throws ExportException
     * @covers ::processLine
     * @dataProvider provideProcessLine
     */
    public function testProcessLine(string $outputLine, array $modNames, array $expectedModNames): void
    {
        $dump = new Dump();
        $dump->modNames = $modNames;

        $instance = new ModNameOutputProcessor();
        $instance->processLine($outputLine, $dump);

        $this->assertEquals($expectedModNames, $dump->modNames);
    }

    /**
     * @throws ExportException
     * @covers ::processExitCode
     */
    public function testProcessExitCode(): void
    {
        $dump = new Dump();
        $dump->modNames = ['abc', 'def', 'Dump'];

        $instance = new ModNameOutputProcessor();
        $instance->processExitCode(0, $dump);

        $this->addToAssertionCount(1);
    }

    /**
     * @throws ExportException
     * @covers ::processExitCode
     */
    public function testProcessExitCodeWithException(): void
    {
        $dump = new Dump();
        $dump->modNames = ['abc', 'def'];

        $this->expectException(DumpModNotLoadedException::class);

        $instance = new ModNameOutputProcessor();
        $instance->processExitCode(0, $dump);
    }
}
