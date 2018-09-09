<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Parser;

use BluePsyduck\Common\Data\DataContainer;
use FactorioItemBrowser\Export\I18n\Translator;
use FactorioItemBrowser\ExportData\Entity\Machine;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;
use FactorioItemBrowser\ExportData\Registry\EntityRegistry;

/**
 * The class parsing the machines of the dump.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class MachineParser implements ParserInterface
{
    /**
     * The units of the energy usage to use.
     */
    protected const ENERGY_USAGE_UNITS = ['W', 'kW', 'MW', 'GW', 'TW', 'PW', 'EW', 'ZW', 'YW'];

    /**
     * The machine registry.
     * @var EntityRegistry
     */
    protected $machineRegistry;

    /**
     * The translator.
     * @var Translator
     */
    protected $translator;

    /**
     * Initializes the parser.
     * @param EntityRegistry $machineRegistry
     * @param Translator $translator
     */
    public function __construct(EntityRegistry $machineRegistry, Translator $translator)
    {
        $this->machineRegistry = $machineRegistry;
        $this->translator = $translator;
    }

    /**
     * Parses the dump data into the combination.
     * @param Combination $combination
     * @param DataContainer $dumpData
     */
    public function parse(Combination $combination, DataContainer $dumpData): void
    {
        $machines = [];
        foreach ($dumpData->getObjectArray('machines') as $machineData) {
            $machine = $this->parseMachine($machineData);
            $machines[$machine->getName()] = $machine;
        }
        foreach ($dumpData->getObjectArray('fluidBoxes') as $fluidBoxData) {
            $name = strtolower($fluidBoxData->getString('name'));
            if (isset($machines[$name])) {
                $this->parseFluidBox($machines[$name], $fluidBoxData);
            }
        }
        foreach ($machines as $machine) {
            $combination->addMachineHash($this->machineRegistry->set($machine));
        }
    }

    /**
     * Parses the specified data into an machine entity.
     * @param DataContainer $machineData
     * @return Machine
     */
    protected function parseMachine(DataContainer $machineData): Machine
    {
        $machine = new Machine();
        $machine->setName(strtolower($machineData->getString('name')))
                ->setCraftingSpeed($machineData->getFloat('craftingSpeed', 1.))
                ->setNumberOfItemSlots($machineData->getInteger('numberOfItemSlots', 0))
                ->setNumberOfModuleSlots($machineData->getInteger('numberOfModuleSlots', 0));

        foreach ($machineData->getArray('craftingCategories') as $craftingCategory => $isEnabled) {
            if ((bool) $isEnabled && strlen($craftingCategory) > 0) {
                $machine->addCraftingCategory($craftingCategory);
            }
        }
        $this->parseEnergyUsage($machine, $machineData);

        $this->translator->addTranslationsToEntity(
            $machine->getLabels(),
            'name',
            $machineData->get(['localised', 'name'])
        );
        $this->translator->addTranslationsToEntity(
            $machine->getDescriptions(),
            'description',
            $machineData->get(['localised', 'description'])
        );
        return $machine;
    }

    /**
     * Parses the energy usage into the specified machine.
     * @param Machine $machine
     * @param DataContainer $machineData
     */
    protected function parseEnergyUsage(Machine $machine, DataContainer $machineData): void
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
    }

    /**
     * Parses the fluid box data into the machine.
     * @param Machine $machine
     * @param DataContainer $fluidBoxData
     */
    protected function parseFluidBox(Machine $machine, DataContainer $fluidBoxData): void
    {
        $machine->setNumberOfFluidInputSlots($fluidBoxData->getInteger('input'))
                ->setNumberOfFluidOutputSlots($fluidBoxData->getInteger('output'));
    }
}
