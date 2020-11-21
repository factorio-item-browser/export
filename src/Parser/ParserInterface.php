<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Parser;

use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\ExportData\ExportData;

/**
 * The interface of the parsers.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
interface ParserInterface
{
    /**
     * Prepares the parser to be able to later parse the dump.
     * @param Dump $dump
     * @throws ExportException
     */
    public function prepare(Dump $dump): void;

    /**
     * Parses the data from the dump into the combination.
     * @param Dump $dump
     * @param ExportData $exportData
     * @throws ExportException
     */
    public function parse(Dump $dump, ExportData $exportData): void;

    /**
     * Validates the data in the combination as a second parsing step.
     * @param ExportData $exportData
     * @throws ExportException
     */
    public function validate(ExportData $exportData): void;
}
