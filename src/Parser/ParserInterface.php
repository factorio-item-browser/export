<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Parser;

use BluePsyduck\Common\Data\DataContainer;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;

/**
 * The interface of the parsers.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
interface ParserInterface
{
    /**
     * Parses the data from the dump into actual entities.
     * @param DataContainer $dumpData
     */
    public function parse(DataContainer $dumpData): void;

    /**
     * Checks the parsed data.
     */
    public function check(): void;

    /**
     * Persists the parsed data into the combination.
     * @param Combination $combination
     */
    public function persist(Combination $combination): void;
}
