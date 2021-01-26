<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command\ProcessStep;

use FactorioItemBrowser\CombinationApi\Client\Constant\JobStatus;
use FactorioItemBrowser\Export\Entity\ProcessStepData;
use FactorioItemBrowser\Export\Parser\ParserInterface;

/**
 * The step for parsing the dumped data.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ParserStep implements ProcessStepInterface
{
    /** @var array<ParserInterface> */
    private array $parsers;

    /**
     * @param array<ParserInterface> $exportParsers
     */
    public function __construct(array $exportParsers)
    {
        $this->parsers = $exportParsers;
    }

    public function getLabel(): string
    {
        return 'Parsing the dumped data';
    }

    public function getExportJobStatus(): string
    {
        return JobStatus::PROCESSING;
    }

    public function run(ProcessStepData $processStepData): void
    {
        foreach ($this->parsers as $parser) {
            $parser->prepare($processStepData->dump);
        }

        foreach ($this->parsers as $parser) {
            $parser->parse($processStepData->dump, $processStepData->exportData);
        }

        foreach ($this->parsers as $parser) {
            $parser->validate($processStepData->exportData);
        }
    }
}
