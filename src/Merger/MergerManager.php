<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Merger;

use FactorioItemBrowser\Export\Entity\ExportCombination;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;
use FactorioItemBrowser\ExportData\Entity\Mod\CombinationData;
use FactorioItemBrowser\ExportData\Exception\ExportDataException;
use FactorioItemBrowser\ExportData\Service\ExportDataService;

/**
 * The class managing the mergers of combinations.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class MergerManager
{
    /**
     * The export data service.
     * @var ExportDataService
     */
    protected $exportDataService;

    /**
     * The mergers.
     * @var AbstractMerger[]
     */
    protected $mergers;

    /**
     * Initializes the merger manager.
     * @param ExportDataService $exportDataService
     * @param array|AbstractMerger[] $mergers
     */
    public function __construct(ExportDataService $exportDataService, array $mergers)
    {
        $this->exportDataService = $exportDataService;
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

    /**
     * Merged the parent combinations of the specified one.
     * @param ExportCombination $exportCombination
     * @return Combination
     * @throws ExportDataException
     */
    public function mergeParentCombinations(ExportCombination $exportCombination): Combination
    {
        $mergedCombination = new Combination();
        foreach ($exportCombination->getParentCombinations() as $parentCombination) {
            if (!$parentCombination instanceof ExportCombination) {
                $this->exportDataService->loadCombinationData($parentCombination);
            }
            $this->merge($mergedCombination->getData(), $parentCombination->getData());
        }

        return $mergedCombination;
    }
}
