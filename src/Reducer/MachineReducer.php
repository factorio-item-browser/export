<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Reducer;

use FactorioItemBrowser\Export\Exception\ReducerException;
use FactorioItemBrowser\ExportData\Entity\EntityInterface;
use FactorioItemBrowser\ExportData\Entity\Machine;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;
use FactorioItemBrowser\ExportData\Utils\EntityUtils;

/**
 * The class removing any machines which did not change.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class MachineReducer extends AbstractIdentifiedEntityReducer
{
    use LocalisedStringReducerTrait;
    
    /**
     * Returns the hashes to use from the specified combination.
     * @param Combination $combination
     * @return array|string[]
     */
    protected function getHashesFromCombination(Combination $combination): array
    {
        return $combination->getMachineHashes();
    }

    /**
     * Reduces the entity against its parent.
     * @param EntityInterface $entity
     * @param EntityInterface $parentEntity
     * @throws ReducerException
     */
    protected function reduceEntity(EntityInterface $entity, EntityInterface $parentEntity): void
    {
        if (!$entity instanceof Machine || !$parentEntity instanceof Machine) {
            throw new ReducerException('Internal type error.');
        }

        $this->reduceDataOfMachine($entity, $parentEntity);
        $this->reduceTranslationsOfMachine($entity, $parentEntity);
        $this->reduceIconOfMachine($entity, $parentEntity);
    }

    /**
     * Reduces the data of the machine.
     * @param Machine $machine
     * @param Machine $parentMachine
     */
    protected function reduceDataOfMachine(Machine $machine, Machine $parentMachine): void
    {
        if ($this->calculateDataHash($machine) === $this->calculateDataHash($parentMachine)) {
            $machine->setCraftingCategories([])
                    ->setCraftingSpeed(1.)
                    ->setNumberOfItemSlots(0)
                    ->setNumberOfFluidInputSlots(0)
                    ->setNumberOfFluidOutputSlots(0)
                    ->setNumberOfModuleSlots(0)
                    ->setEnergyUsage(0)
                    ->setEnergyUsageUnit('');
        }
    }

    /**
     * Calculates a data hash of the specified machine.
     * @param Machine $machine
     * @return string
     */
    protected function calculateDataHash(Machine $machine): string
    {
        $craftingCategories = $machine->getCraftingCategories();
        sort($craftingCategories);

        return EntityUtils::calculateHashOfArray([
            'cc' => array_values($craftingCategories),
            'cs' => $machine->getCraftingSpeed(),
            'ni' => $machine->getNumberOfItemSlots(),
            'fi' => $machine->getNumberOfFluidInputSlots(),
            'fo' => $machine->getNumberOfFluidOutputSlots(),
            'nm' => $machine->getNumberOfModuleSlots(),
            'eu' => $machine->getEnergyUsage(),
            'un' => $machine->getEnergyUsageUnit(),
        ]);
    }

    /**
     * Reduces the translations of the machine.
     * @param Machine $machine
     * @param Machine $parentMachine
     */
    protected function reduceTranslationsOfMachine(Machine $machine, Machine $parentMachine): void
    {
        $this->reduceLocalisedString($machine->getLabels(), $parentMachine->getLabels());
        $this->reduceLocalisedString($machine->getDescriptions(), $parentMachine->getDescriptions());
    }

    /**
     * Reduces the icon of the machine.
     * @param Machine $machine
     * @param Machine $parentMachine
     */
    protected function reduceIconOfMachine(Machine $machine, Machine $parentMachine): void
    {
        if ($machine->getIconHash() === $parentMachine->getIconHash()) {
            $machine->setIconHash('');
        }
    }

    /**
     * Sets the hashes to the combination.
     * @param Combination $combination
     * @param array|string[] $hashes
     */
    protected function setHashesToCombination(Combination $combination, array $hashes): void
    {
        $combination->setMachineHashes($hashes);
    }
}
