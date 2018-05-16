<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Parser;

use BluePsyduck\Common\Data\DataContainer;
use FactorioItemBrowser\ExportData\Entity\Machine;
use FactorioItemBrowser\ExportData\Entity\Mod\CombinationData;

/**
 * The class parsing the machines of the dump.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class MachineParser extends AbstractParser
{
    /**
     * Parses the dump data into the combination.
     * @param CombinationData $combinationData
     * @param DataContainer $dumpData
     * @return $this
     */
    public function parse(CombinationData $combinationData, DataContainer $dumpData)
    {
        foreach ($dumpData->getObjectArray('machines') as $machineData) {
            $combinationData->addMachine($this->parseMachine($machineData));
        }
        return $this;
    }

    /**
     * Parses the specified data into an machine entity.
     * @param DataContainer $machineData
     * @return Machine
     */
    protected function parseMachine(DataContainer $machineData): Machine
    {
        $machine = new Machine();
        $machine->setName($machineData->getString('name'))
                ->setCraftingSpeed($machineData->getFloat('craftingSpeed', 1.))
                ->setNumberOfIngredientSlots($machineData->getInteger('numberOfIngredientSlots', 1))
                ->setNumberOfModuleSlots($machineData->getInteger('numberOfModuleSlots', 0))
                ->setEnergyUsage($machineData->getInteger('energyUsage', 0));

        foreach ($machineData->getArray('craftingCategories') as $craftingCategory => $isEnabled) {
            if ($isEnabled) {
                $machine->addCraftingCategory($craftingCategory);
            }
        }

        $this->translator->addTranslations(
            $machine->getLabels(),
            'name',
            $machineData->get(['localised', 'name']),
            ''
        );
        $this->translator->addTranslations(
            $machine->getDescriptions(),
            'description',
            $machineData->get(['localised', 'description']),
            ''
        );
        return $machine;
    }
}