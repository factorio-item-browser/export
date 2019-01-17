<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Merger;

use FactorioItemBrowser\Export\Exception\MergerException;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;

/**
 * The interface of the mergers.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
interface MergerInterface
{
    /**
     * Merges the source combination into the destination one.
     * @param Combination $destination
     * @param Combination $source
     * @throws MergerException
     */
    public function merge(Combination $destination, Combination $source): void;
}
