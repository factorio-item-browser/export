<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command\ProcessStep;

use FactorioItemBrowser\Export\Entity\ProcessStepData;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Parser\ParserManager;
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
     * The parser manager.
     * @var ParserManager
     */
    protected $parserManager;

    /**
     * Initializes the step.
     * @param ParserManager $parserManager
     */
    public function __construct(ParserManager $parserManager)
    {
        $this->parserManager = $parserManager;
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
        $this->parserManager->parse($processStepData->getDump(), $processStepData->getExportData()->getCombination());
    }
}
