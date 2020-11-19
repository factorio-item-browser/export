<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command\ProcessStep;

use FactorioItemBrowser\Export\Console\Console;
use FactorioItemBrowser\Export\Entity\ProcessStepData;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Parser\ParserInterface;
use FactorioItemBrowser\ExportQueue\Client\Constant\JobStatus;

/**
 * The step for parsing the dumped data.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ParserStep implements ProcessStepInterface
{
    /**
     * The console.
     * @var Console
     */
    protected $console;

    /**
     * The parsers to use.
     * @var array|ParserInterface[]
     */
    protected $parsers;

    /**
     * Initializes the step.
     * @param Console $console
     * @param array|ParserInterface[] $exportParsers
     */
    public function __construct(Console $console, array $exportParsers)
    {
        $this->console = $console;
        $this->parsers = $exportParsers;
    }

    /**
     * Returns the label to identify the step.
     * @return string
     */
    public function getLabel(): string
    {
        return 'Parsing the dumped data';
    }

    /**
     * Returns the status to set on the export job before running this step.
     * @return string
     */
    public function getExportJobStatus(): string
    {
        return JobStatus::PROCESSING;
    }

    /**
     * Runs the process step.
     * @param ProcessStepData $processStepData
     * @throws ExportException
     */
    public function run(ProcessStepData $processStepData): void
    {
        $this->console->writeAction('Preparing');
        foreach ($this->parsers as $parser) {
            $parser->prepare($processStepData->getDump());
        }

        $this->console->writeAction('Parsing');
        foreach ($this->parsers as $parser) {
            $parser->parse($processStepData->getDump(), $processStepData->getExportData());
        }

        $this->console->writeAction('Validating');
        foreach ($this->parsers as $parser) {
            $parser->validate($processStepData->getExportData());
        }
    }
}
