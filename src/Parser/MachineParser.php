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
    /**
     * The icon parser.
     * @var IconParser
     */
    protected $iconParser;

    /**
     * The translation parser.
     * @var TranslationParser
     */
    protected $translationParser;

    /**
     * Initializes the parser.
     * @param IconParser $iconParser
     * @param TranslationParser $translationParser
     */
    public function __construct(IconParser $iconParser, TranslationParser $translationParser)
    {
        $this->iconParser = $iconParser;
        $this->translationParser = $translationParser;
    }

    /**
     * Prepares the parser to be able to later parse the dump.
     * @param Dump $dump
     */
    public function prepare(Dump $dump): void
    {
    }

    /**
     * Parses the data from the dump into the combination.
     * @param Dump $dump
     * @param Combination $combination
     */
    public function parse(Dump $dump, Combination $combination): void
    {
        foreach ($dump->getControlStage()->getMachines() as $dumpMachine) {
            $combination->addMachine($this->mapMachine($dumpMachine));
        }
    }

    /**
     * Maps the dump machine to an export one.
     * @param DumpMachine $dumpMachine
     * @return ExportMachine
     */
    protected function mapMachine(DumpMachine $dumpMachine): ExportMachine
    {
        $exportMachine = new ExportMachine();
        $exportMachine->setName(strtolower($dumpMachine->getName()))
                      ->setCraftingCategories($dumpMachine->getCraftingCategories())
                      ->setCraftingSpeed($dumpMachine->getCraftingSpeed())
                      ->setNumberOfItemSlots($dumpMachine->getItemSlots())
                      ->setNumberOfFluidInputSlots($dumpMachine->getFluidInputSlots())
                      ->setNumberOfFluidOutputSlots($dumpMachine->getFluidOutputSlots())
                      ->setNumberOfModuleSlots($dumpMachine->getModuleSlots())
                      ->setIconId(
                          $this->iconParser->getIconId(EntityType::MACHINE, strtolower($dumpMachine->getName()))
                      );

        $this->mapEnergyUsage($exportMachine, $dumpMachine->getEnergyUsage());
        $this->translationParser->translateNames($exportMachine->getLabels(), $dumpMachine->getLocalisedName());
        $this->translationParser->translateNames(
            $exportMachine->getDescriptions(),
            $dumpMachine->getLocalisedDescription()
        );

        return $exportMachine;
    }

    /**
     * Parses the energy usage into the specified machine.
     * @param ExportMachine $exportMachine
     * @param float $energyUsage
     */
    protected function mapEnergyUsage(ExportMachine $exportMachine, float $energyUsage): void
    {
        $units = EnergyUsageUnit::ORDERED_UNITS;
        $currentUnit = array_shift($units);
        while ($energyUsage >= 1000 && count($units) > 0) {
            $energyUsage /= 1000;
            $currentUnit = array_shift($units);
        }

        $exportMachine->setEnergyUsage(round($energyUsage, 3))
                      ->setEnergyUsageUnit($currentUnit);
    }

    /**
     * Validates the data in the combination as a second parsing step.
     * @param Combination $combination
     */
    public function validate(Combination $combination): void
    {
        foreach ($combination->getMachines() as $machine) {
            $machine->setIconId($this->iconParser->getIconId(EntityType::MACHINE, $machine->getName()));
        }
    }
}
