<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Parser;

use BluePsyduck\Common\Data\DataContainer;
use FactorioItemBrowser\ExportData\Entity\Item;
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
     * The units of the energy usage to use.
     */
    private const ENERGY_USAGE_UNITS = ['W', 'kW', 'MW', 'GW', 'TW', 'PW', 'EW', 'ZW', 'YW'];

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
        foreach ($dumpData->getObjectArray('fluidBoxes') as $fluidBoxData) {
            $machine = $combinationData->getMachine($fluidBoxData->getString('name'));
            if ($machine instanceof Machine) {
                $this->parseFluidBox($machine, $fluidBoxData);
            }
        }

        $this->removeDuplicateTranslations($combinationData);
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
                ->setNumberOfItemSlots($machineData->getInteger('numberOfItemSlots', 0))
                ->setNumberOfModuleSlots($machineData->getInteger('numberOfModuleSlots', 0));

        foreach ($machineData->getArray('craftingCategories') as $craftingCategory => $isEnabled) {
            if ($isEnabled && strlen($craftingCategory) > 0) {
                $machine->addCraftingCategory($craftingCategory);
            }
        }
        $this->parseEnergyUsage($machine, $machineData);

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

    /**
     * Parses the energy usage into the specified machine.
     * @param Machine $machine
     * @param DataContainer $machineData
     * @return $this
     */
    protected function parseEnergyUsage(Machine $machine, DataContainer $machineData)
    {
        $energyUsage = $machineData->getFloat('energyUsage', 0.); // Float because numbers may be bigger than 64bit
        if ($energyUsage > 0) {
            $units = self::ENERGY_USAGE_UNITS;
            $currentUnit = array_shift($units);
            while ($energyUsage >= 1000 && count($units) > 0) {
                $energyUsage /= 1000;
                $currentUnit = array_shift($units);
            }

            $machine->setEnergyUsage(round($energyUsage, 3))
                    ->setEnergyUsageUnit($currentUnit);
        }
        return $this;
    }

    /**
     * Parses the fluid box data into the machine.
     * @param Machine $machine
     * @param DataContainer $fluidBoxData
     * @return $this
     */
    protected function parseFluidBox(Machine $machine, DataContainer $fluidBoxData)
    {
        $machine->setNumberOfFluidInputSlots($fluidBoxData->getInteger('input'))
                ->setNumberOfFluidOutputSlots($fluidBoxData->getInteger('output'));
        return $this;
    }

    /**
     * Removes duplicate translations if the item are already providing them.
     * @param CombinationData $combinationData
     * @return $this
     */
    protected function removeDuplicateTranslations(CombinationData $combinationData)
    {
        foreach ($combinationData->getMachines() as $machine) {
            $item = $combinationData->getItem('item', $machine->getName());
            if ($item instanceof Item
                && $this->areLocalisedStringsIdentical($item->getLabels(), $machine->getLabels())
                && $this->areLocalisedStringsIdentical($item->getDescriptions(), $machine->getDescriptions())
            ) {
                $machine->getLabels()->readData(new DataContainer([]));
                $machine->getDescriptions()->readData(new DataContainer([]));
                $item->setProvidesMachineLocalisation(true);
            }
        }
        return $this;
    }
}