<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Merger;

use FactorioItemBrowser\ExportData\Entity\Machine;
use FactorioItemBrowser\ExportData\Entity\Mod\CombinationData;

/**
 * The class merging machines of combinations.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class MachineMerger extends AbstractMerger
{
    /**
     * Merges the source combination data into the destination one.
     * @param CombinationData $destination
     * @param CombinationData $source
     * @return $this
     */
    public function merge(CombinationData $destination, CombinationData $source)
    {
        foreach ($source->getMachines() as $sourceMachine) {
            $destinationMachine = $destination->getMachine($sourceMachine->getName());
            if ($destinationMachine instanceof Machine) {
                $this->mergeMachine($destinationMachine, $sourceMachine);
            } else {
                $destination->addMachine(clone($sourceMachine));
            }
        }
        return $this;
    }

    /**
     * Merges the source machine into the destination one.
     * @param Machine $destination
     * @param Machine $source
     * @return $this
     */
    protected function mergeMachine(Machine $destination, Machine $source)
    {
        if (strlen($source->getIconHash()) > 0) {
            $destination->setIconHash($source->getIconHash());
        }
        if (count($source->getCraftingCategories()) > 0) {
            $destination->setCraftingCategories($source->getCraftingCategories())
                        ->setCraftingSpeed($source->getCraftingSpeed())
                        ->setNumberOfIngredientSlots($source->getNumberOfIngredientSlots())
                        ->setNumberOfModuleSlots($source->getNumberOfModuleSlots())
                        ->setEnergyUsage($source->getEnergyUsage());
        }

        $this->mergeLocalisedString($destination->getLabels(), $source->getLabels());
        $this->mergeLocalisedString($destination->getDescriptions(), $source->getDescriptions());
        return $this;
    }
}