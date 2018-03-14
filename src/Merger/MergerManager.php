<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Merger;

use FactorioItemBrowser\ExportData\Entity\Mod\CombinationData;

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
     * Merges the source combination data into the destination one.
     * @param CombinationData $destination
     * @param CombinationData $source
     * @return $this
     */
    public function merge(CombinationData $destination, CombinationData $source)
    {
        foreach ($this->mergers as $merger) {
            $merger->merge($destination, $source);
        }
        return $this;
    }
}