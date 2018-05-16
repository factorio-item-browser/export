<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Reducer;

use FactorioItemBrowser\ExportData\Entity\Machine;
use FactorioItemBrowser\ExportData\Entity\Mod\CombinationData;

/**
 * The class removing any machines which did not change.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class MachineReducer extends AbstractReducer
{
    /**
     * Reduces the specified combination data, removing any data which is identical in the parent combination.
     * @param CombinationData $combination
     * @param CombinationData $parentCombination
     * @return $this
     */
    public function reduce(CombinationData $combination, CombinationData $parentCombination)
    {
        foreach ($parentCombination->getMachines() as $parentMachine) {
            $machine = $combination->getMachine($parentMachine->getName());
            if ($machine instanceof Machine) {
                $this->reduceLocalisedString($machine->getLabels(), $parentMachine->getLabels());
                $this->reduceLocalisedString($machine->getDescriptions(), $parentMachine->getDescriptions());
                if ($machine->getIconHash() === $parentMachine->getIconHash()) {
                    $machine->setIconHash('');
                }

                if ($this->calculateHash($machine) === $this->calculateHash($parentMachine)) {
                    if (count($machine->getLabels()->getTranslations()) === 0
                        && count($machine->getDescriptions()->getTranslations()) === 0
                        && strlen($machine->getIconHash()) === 0
                    ) {
                        $combination->removeMachine($machine->getName());
                    } else {
                        $machine->setCraftingCategories([])
                                ->setCraftingSpeed(1.)
                                ->setNumberOfIngredientSlots(1)
                                ->setNumberOfModuleSlots(0)
                                ->setEnergyUsage(0);
                    }
                }
            }
        }

        $combination->setMachines(array_values($combination->getMachines()));
        return $this;
    }

    /**
     * Calculates a hash of the specified machine.
     * @param Machine $machine
     * @return string
     */
    protected function calculateHash(Machine $machine): string
    {
        $craftingCategories = $machine->getCraftingCategories();
        sort($craftingCategories);

        $data = [
            'cc' => array_values($craftingCategories),
            'cs' => $machine->getCraftingSpeed(),
            'ni' => $machine->getNumberOfIngredientSlots(),
            'nm' => $machine->getNumberOfModuleSlots(),
            'eu' => $machine->getEnergyUsage()
        ];
        return hash('crc32b', json_encode($data));
    }
}