<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\OutputProcessor;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Exception\FactorioExecutionException;
use FactorioItemBrowser\Export\OutputProcessor\ErrorOutputProcessor;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the ErrorOutputProcessor class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\OutputProcessor\ErrorOutputProcessor
 */
class ErrorOutputProcessorTest extends TestCase
{
    use ReflectionTrait;

    /**
     * @return array<mixed>
     */
    public function provideProcessLine(): array
    {
        $output1 = <<<EOT
   0.007 Running in headless mode
   0.009 Loading mod core 0.0.0 (data.lua)
   0.061 Loading mod base 0.18.21 (data.lua)
   0.235 Error Util.cpp:83: Failed to load mod "base": __base__/data.lua:89: __base__/prototypes/...
stack traceback:
	[C]: in function 'require'
	__base__/data.lua:89: in main chunk
EOT;
        $message1 = <<<EOT
   0.235 Error Util.cpp:83: Failed to load mod "base": __base__/data.lua:89: __base__/prototypes/...
stack traceback:
	[C]: in function 'require'
	__base__/data.lua:89: in main chunk
EOT;

        $output2 = <<<EOT
   2.399 Checksum for script /project/data/instances/2f4a45fa-a509-a9d1-aae6-ffcf984a7a76/...
   2.401 Checksum for script __Dump__/control.lua: 3285258963
Error: The mod Factorio Item Browser - Dump (1.0.0) caused a non-recoverable error.
Please report this error to the mod author.

Error while running event Dump::on_init()
__Dump__/map.lua:82: attempt to index global 'prototype2' (a nil value)
stack traceback:
	__Dump__/map.lua:82: in function 'recipe'
	__Dump__/control.lua:37: in function <__Dump__/control.lua:4>
   2.448 Goodbye
EOT;
        $message2 = <<<EOT
Error: The mod Factorio Item Browser - Dump (1.0.0) caused a non-recoverable error.
Please report this error to the mod author.

Error while running event Dump::on_init()
__Dump__/map.lua:82: attempt to index global 'prototype2' (a nil value)
stack traceback:
	__Dump__/map.lua:82: in function 'recipe'
	__Dump__/control.lua:37: in function <__Dump__/control.lua:4>
   2.448 Goodbye
EOT;

        $output3 = <<<EOT
   0.007 Running in headless mode
   0.009 Loading mod core 0.0.0 (data.lua)
   0.061 Loading mod base 0.18.21 (data.lua)
EOT;

        return [
            [explode(PHP_EOL, $output1), explode(PHP_EOL, $message1)],
            [explode(PHP_EOL, $output2), explode(PHP_EOL, $message2)],
            [explode(PHP_EOL, $output3), []],
        ];
    }

    /**
     * @param array<string> $lines
     * @param array<string> $expectedLines
     * @throws ExportException
     * @throws ReflectionException
     * @covers ::processLine
     * @dataProvider provideProcessLine
     */
    public function testProcessLine(array $lines, array $expectedLines): void
    {
        $dump = $this->createMock(Dump::class);

        $processor = new ErrorOutputProcessor();
        foreach ($lines as $line) {
            $processor->processLine($line, $dump);
        }

        $this->assertEquals($expectedLines, $this->extractProperty($processor, 'errorLines'));
    }

    /**
     * @throws ExportException
     * @throws ReflectionException
     * @covers ::processExitCode
     */
    public function testProcessExitCode(): void
    {
        $exitCode = 42;
        $errorLines = ['abc', 'def'];
        $dump = $this->createMock(Dump::class);

        $this->expectException(FactorioExecutionException::class);
        $this->expectExceptionCode($exitCode);
        $this->expectExceptionMessage("abc\ndef");

        $processor = new ErrorOutputProcessor();
        $this->injectProperty($processor, 'errorLines', $errorLines);

        $processor->processExitCode($exitCode, $dump);
    }

    /**
     * @throws ExportException
     * @throws ReflectionException
     * @covers ::processExitCode
     */
    public function testProcessExitCodeWithoutException(): void
    {
        $exitCode = 0;
        $errorLines = ['abc', 'def'];
        $dump = $this->createMock(Dump::class);

        $processor = new ErrorOutputProcessor();
        $this->injectProperty($processor, 'errorLines', $errorLines);

        $processor->processExitCode($exitCode, $dump);
        $this->addToAssertionCount(1);
    }
}
