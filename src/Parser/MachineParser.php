<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Parser;

use FactorioItemBrowser\Common\Constant\EnergyUsageUnit;
use FactorioItemBrowser\Common\Constant\EntityType;
use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\Entity\Dump\Machine as DumpMachine;
use FactorioItemBrowser\ExportData\Entity\Combination;
use FactorioItemBrowser\ExportData\Entity\Machine as ExportMachine;

/**
 * The parser of the machines.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class MachineParser implements ParserInterface
{
    protected IconParser $iconParser;
    protected TranslationParser $translationParser;

    public function __construct(IconParser $iconParser, TranslationParser $translationParser)
    {
        $this->iconParser = $iconParser;
        $this->translationParser = $translationParser;
    }

    public function prepare(Dump $dump): void
    {
    }

    public function parse(Dump $dump, Combination $combination): void
    {
        foreach ($dump->machines as $dumpMachine) {
            $combination->addMachine($this->mapMachine($dumpMachine));
        }
    }

    protected function mapMachine(DumpMachine $dumpMachine): ExportMachine
    {
        $exportMachine = new ExportMachine();
        $exportMachine->setName($dumpMachine->name)
                      ->setCraftingCategories($dumpMachine->craftingCategories)
                      ->setCraftingSpeed($dumpMachine->craftingSpeed)
                      ->setNumberOfItemSlots($dumpMachine->itemSlots)
                      ->setNumberOfFluidInputSlots($dumpMachine->fluidInputSlots)
                      ->setNumberOfFluidOutputSlots($dumpMachine->fluidOutputSlots)
                      ->setNumberOfModuleSlots($dumpMachine->moduleSlots)
                      ->setIconId(
                          $this->iconParser->getIconId(EntityType::MACHINE, $dumpMachine->name)
                      );

        $this->mapEnergyUsage($exportMachine, $dumpMachine->energyUsage);
        $this->translationParser->translate($exportMachine->getLabels(), $dumpMachine->localisedName);
        $this->translationParser->translate($exportMachine->getDescriptions(), $dumpMachine->localisedDescription);

        return $exportMachine;
    }

    protected function mapEnergyUsage(ExportMachine $exportMachine, float $energyUsage): void
    {
        $unit = EnergyUsageUnit::WATT;
        foreach (EnergyUsageUnit::ORDERED_UNITS as $currentUnit) {
            if ($energyUsage < 1000) {
                $unit = $currentUnit;
                break;
            }
            $energyUsage /= 1000;
        }

        $exportMachine->setEnergyUsage(round($energyUsage, 3))
                      ->setEnergyUsageUnit($unit);
    }

    public function validate(Combination $combination): void
    {
    }
}
