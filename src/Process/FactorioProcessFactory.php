<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Process;

use FactorioItemBrowser\Export\OutputProcessor\OutputProcessorInterface;

/**
 * The factory for the FactorioProcess class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class FactorioProcessFactory
{
    /** @var array<OutputProcessorInterface> */
    private array $outputProcessors;

    /**
     * @param array<OutputProcessorInterface> $exportOutputProcessors
     */
    public function __construct(array $exportOutputProcessors)
    {
        $this->outputProcessors = $exportOutputProcessors;
    }

    public function create(string $instanceDirectory): FactorioProcess
    {
        return new FactorioProcess($this->outputProcessors, $instanceDirectory);
    }
}
