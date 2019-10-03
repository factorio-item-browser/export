<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Parser;

use FactorioItemBrowser\Export\Console\Console;
use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\ExportData\Entity\Combination;

/**
 * The manager of the parser classes.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ParserManager
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
     * Initializes the parser manager.
     * @param Console $console
     * @param array|ParserInterface[] $exportParsers
     */
    public function __construct(Console $console, array $exportParsers)
    {
        $this->console = $console;
        $this->parsers = $exportParsers;
    }

    /**
     * Parses the dump into the combination.
     * @param Dump $dump
     * @param Combination $combination
     * @throws ExportException
     */
    public function parse(Dump $dump, Combination $combination): void
    {
        $this->console->writeAction('Preparing');
        foreach ($this->parsers as $parser) {
            $parser->prepare($dump);
        }

        $this->console->writeAction('Parsing');
        foreach ($this->parsers as $parser) {
            $parser->parse($dump, $combination);
        }

        $this->console->writeAction('Validating');
        foreach ($this->parsers as $parser) {
            $parser->validate($combination);
        }
    }
}
