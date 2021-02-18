<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Process;

use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\OutputProcessor\OutputProcessorInterface;
use Symfony\Component\Process\Process;

/**
 * The process actually launching the Factorio game.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class FactorioProcess
{
    /** @var array<OutputProcessorInterface> */
    private array $outputProcessors;

    private Dump $dump;
    /** @var Process<string>  */
    private Process $process;

    /**
     * @param array<OutputProcessorInterface> $outputProcessors
     * @param string $instanceDirectory
     */
    public function __construct(array $outputProcessors, string $instanceDirectory)
    {
        $this->dump = new Dump();
        $this->outputProcessors = $outputProcessors;

        $this->process = new Process([
            $instanceDirectory . '/bin/x64/factorio',
            '--no-log-rotation',
            '--create=' . $instanceDirectory . '/dump',
            '--mod-directory=' . $instanceDirectory . '/mods',
        ]);
        $this->process->setTimeout(null);
    }

    /**
     * @throws ExportException
     */
    public function run(): void
    {
        $this->process->run([$this, 'handleOutput']);

        $exitCode = (int) $this->process->getExitCode();
        foreach ($this->outputProcessors as $outputProcessor) {
            $outputProcessor->processExitCode($exitCode, $this->dump);
        }
    }

    /**
     * @param string $type
     * @param string $contents
     * @throws ExportException
     */
    public function handleOutput(string $type, string $contents): void
    {
        if ($type === Process::OUT) {
            foreach (explode(PHP_EOL, $contents) as $content) {
                if ($content !== "") {
                    foreach ($this->outputProcessors as $outputProcessor) {
                        $outputProcessor->processLine($content, $this->dump);
                    }
                }
            }
        }
    }

    public function getDump(): Dump
    {
        return $this->dump;
    }
}
