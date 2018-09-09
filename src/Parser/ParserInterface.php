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
     * Parses the dump data into the combination.
     * @param Combination $combination
     * @param DataContainer $dumpData
     */
    public function parse(Combination $combination, DataContainer $dumpData): void;
}
