<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\OutputProcessor;

use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\OutputProcessor\DumpOutputProcessor;
use FactorioItemBrowser\Export\OutputProcessor\DumpProcessor\DumpProcessorInterface;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the DumpOutputProcessor class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\OutputProcessor\DumpOutputProcessor
 */
class DumpOutputProcessorTest extends TestCase
{

    /**
     * @throws ExportException
     * @covers ::__construct
     * @covers ::processLine
     */
    public function testProcessLine(): void
    {
        $outputLine = '>DUMP>def>foo<';
        $serializedString = 'foo';
        $dump = $this->createMock(Dump::class);

        $processor1 = $this->createMock(DumpProcessorInterface::class);
        $processor1->expects($this->any())
                   ->method('getType')
                   ->willReturn('abc');
        $processor1->expects($this->never())
                   ->method('process');

        $processor2 = $this->createMock(DumpProcessorInterface::class);
        $processor2->expects($this->any())
                   ->method('getType')
                   ->willReturn('def');
        $processor2->expects($this->once())
                   ->method('process')
                   ->with($this->identicalTo($serializedString), $this->identicalTo($dump));

        $instance = new DumpOutputProcessor([$processor1, $processor2]);
        $instance->processLine($outputLine, $dump);
    }

    /**
     * @throws ExportException
     * @covers ::processLine
     */
    public function testProcessLineWithoutMatch(): void
    {
        $outputLine = '>INVALID>def>foo<';
        $dump = $this->createMock(Dump::class);

        $processor1 = $this->createMock(DumpProcessorInterface::class);
        $processor1->expects($this->any())
                   ->method('getType')
                   ->willReturn('abc');
        $processor1->expects($this->never())
                   ->method('process');

        $processor2 = $this->createMock(DumpProcessorInterface::class);
        $processor2->expects($this->any())
                   ->method('getType')
                   ->willReturn('def');
        $processor2->expects($this->never())
                   ->method('process');

        $instance = new DumpOutputProcessor([$processor1, $processor2]);
        $instance->processLine($outputLine, $dump);
    }

    /**
     * @throws ExportException
     * @covers ::processExitCode
     */
    public function testProcessExitCode(): void
    {
        $instance = new DumpOutputProcessor([]);
        $instance->processExitCode(0, new Dump());

        $this->addToAssertionCount(1);
    }
}
