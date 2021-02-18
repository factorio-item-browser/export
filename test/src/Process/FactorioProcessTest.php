<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Process;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\OutputProcessor\OutputProcessorInterface;
use FactorioItemBrowser\Export\Process\FactorioProcess;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Symfony\Component\Process\Process;

/**
 * The PHPUnit test of the FactorioProcess class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Process\FactorioProcess
 */
class FactorioProcessTest extends TestCase
{
    use ReflectionTrait;

    /**
     * @throws ReflectionException
     * @covers ::__construct
     * @covers ::getDump
     */
    public function testConstruct(): void
    {
        $instanceDirectory = 'abc';
        $outputProcessors = [
            $this->createMock(OutputProcessorInterface::class),
            $this->createMock(OutputProcessorInterface::class),
        ];

        $expectedProcess = new Process([
            'abc/bin/x64/factorio',
            '--no-log-rotation',
            '--create=abc/dump',
            '--mod-directory=abc/mods',
        ], null, null, null, null);

        $process = new FactorioProcess($outputProcessors, $instanceDirectory);

        $this->assertSame($outputProcessors, $this->extractProperty($process, 'outputProcessors'));
        $this->assertEquals($expectedProcess, $this->extractProperty($process, 'process'));

        // Asserted by type-hint
        $process->getDump();
    }

    /**
     * Tests the run method.
     * @throws ExportException|ReflectionException
     * @covers ::run
     */
    public function testRun(): void
    {
        $exitCode = 42;

        $process = $this->createMock(Process::class);

        $process->expects($this->once())
                ->method('getExitCode')
                ->willReturn($exitCode);

        $outputProcessor1 = $this->createMock(OutputProcessorInterface::class);
        $outputProcessor1->expects($this->once())
                         ->method('processExitCode')
                         ->with($this->identicalTo($exitCode), $this->isInstanceOf(Dump::class));

        $outputProcessor2 = $this->createMock(OutputProcessorInterface::class);
        $outputProcessor2->expects($this->once())
                         ->method('processExitCode')
                         ->with($this->identicalTo($exitCode), $this->isInstanceOf(Dump::class));

        $outputProcessors = [$outputProcessor1, $outputProcessor2];

        $instance = new FactorioProcess($outputProcessors, 'abc');
        $this->injectProperty($instance, 'process', $process);

        $process->expects($this->once())
                ->method('run')
                ->with($this->equalTo([$instance, 'handleOutput']));

        $instance->run();
    }

    /**
     * Tests the handleOutput method.
     * @throws ExportException
     * @covers ::handleOutput
     */
    public function testHandleOutput(): void
    {
        $type = Process::OUT;
        $output = "abc\n\ndef\n";

        $outputProcessor1 = $this->createMock(OutputProcessorInterface::class);
        $outputProcessor1->expects($this->exactly(2))
                         ->method('processLine')
                         ->withConsecutive(
                             [$this->identicalTo('abc')],
                             [$this->identicalTo('def')],
                         );
        $outputProcessor2 = $this->createMock(OutputProcessorInterface::class);
        $outputProcessor2->expects($this->exactly(2))
                         ->method('processLine')
                         ->withConsecutive(
                             [$this->identicalTo('abc')],
                             [$this->identicalTo('def')],
                         );

        $outputProcessors = [$outputProcessor1, $outputProcessor2];

        $instance = new FactorioProcess($outputProcessors, 'abc');
        $instance->handleOutput($type, $output);
    }
}
