<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Mapper;

use BluePsyduck\MapperManager\Mapper\StaticMapperInterface;
use FactorioItemBrowser\Common\Constant\EnergyUsageUnit;
use FactorioItemBrowser\Export\Entity\Dump\Machine as DumpMachine;
use FactorioItemBrowser\ExportData\Entity\Machine as ExportMachine;

/**
 * The mapper for the machines.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 *
 * @implements StaticMapperInterface<DumpMachine, ExportMachine>
 */
class MachineMapper implements StaticMapperInterface
{
    public function getSupportedSourceClass(): string
    {
        return DumpMachine::class;
    }

    public function getSupportedDestinationClass(): string
    {
        return ExportMachine::class;
    }

    /**
     * @param DumpMachine $source
     * @param ExportMachine $destination
     */
    public function map(object $source, object $destination): void
    {
        $destination->name = $source->name;
        $destination->craftingCategories = $source->craftingCategories;
        $destination->craftingSpeed = $source->craftingSpeed;
        $destination->numberOfItemSlots = $source->itemSlots;
        $destination->numberOfFluidInputSlots = $source->fluidInputSlots;
        $destination->numberOfFluidOutputSlots = $source->fluidOutputSlots;
        $destination->numberOfModuleSlots = $source->moduleSlots;

        [$destination->energyUsage, $destination->energyUsageUnit] = $this->convertEnergyUsage($source->energyUsage);
    }

    /**
     * @param float $energyUsage
     * @return array{float, string}
     */
    protected function convertEnergyUsage(float $energyUsage): array
    {
        $unit = EnergyUsageUnit::WATT;
        foreach (EnergyUsageUnit::ORDERED_UNITS as $currentUnit) {
            if ($energyUsage < 1000) {
                $unit = $currentUnit;
                break;
            }
            $energyUsage /= 1000;
        }

        return [round($energyUsage, 3), $unit];
    }
}
