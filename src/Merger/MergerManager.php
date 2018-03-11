<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Merger;

use FactorioItemBrowser\ExportData\Entity\Mod\Combination;

/**
 * The class managing the mergers of combinations.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class MergerManager
{
    /**
     * The mergers.
     * @var AbstractMerger[]
     */
    protected $mergers;

    /**
     * Initializes the merger manager.
     * @param array|AbstractMerger[] $mergers
     */
    public function __construct(array $mergers)
    {
        $this->mergers = $mergers;
    }

    /**
     * Merges the source combination into the destination one.
     * @param Combination $destination
     * @param Combination $source
     * @return $this
     */
    public function merge(Combination $destination, Combination $source)
    {
        foreach ($this->mergers as $merger) {
            $merger->merge($destination, $source);
        }
        return $this;
    }
}