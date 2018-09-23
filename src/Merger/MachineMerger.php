<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Merger;

use FactorioItemBrowser\Export\Exception\MergerException;
use FactorioItemBrowser\Export\Utils\LocalisedStringUtils;
use FactorioItemBrowser\ExportData\Entity\EntityInterface;
use FactorioItemBrowser\ExportData\Entity\Machine;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;

/**
 * The class merging machines of combinations.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class MachineMerger extends AbstractIdentifiedEntityMerger
{
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
     * Merges the source entity into the destination one.
     * @param EntityInterface $destination
     * @param EntityInterface $source
     * @throws MergerException
     */
    protected function mergeEntity(EntityInterface $destination, EntityInterface $source): void
    {
        if (!$destination instanceof Machine || !$source instanceof Machine) {
            throw new MergerException('Internal type error.');
        }

        $this->mergeData($destination, $source);
        $this->mergeTranslations($destination, $source);
        $this->mergeIcon($destination, $source);
    }

    /**
     * Merges the actual data of the source machine to the destination one.
     * @param Machine $destination
     * @param Machine $source
     */
    protected function mergeData(Machine $destination, Machine $source): void
    {
        if (count($source->getCraftingCategories()) > 0) {
            $destination->setCraftingCategories($source->getCraftingCategories())
                        ->setCraftingSpeed($source->getCraftingSpeed())
                        ->setNumberOfItemSlots($source->getNumberOfItemSlots())
                        ->setNumberOfFluidInputSlots($source->getNumberOfFluidInputSlots())
                        ->setNumberOfFluidOutputSlots($source->getNumberOfFluidOutputSlots())
                        ->setNumberOfModuleSlots($source->getNumberOfModuleSlots())
                        ->setEnergyUsage($source->getEnergyUsage())
                        ->setEnergyUsageUnit($source->getEnergyUsageUnit());
        }
    }

    /**
     * Merges the translations from the destination machine to the source one.
     * @param Machine $destination
     * @param Machine $source
     */
    protected function mergeTranslations(Machine $destination, Machine $source): void
    {
        LocalisedStringUtils::merge($destination->getLabels(), $source->getLabels());
        LocalisedStringUtils::merge($destination->getDescriptions(), $source->getDescriptions());
    }

    /**
     * Merges the icon from the destination machine to the source one.
     * @param Machine $destination
     * @param Machine $source
     */
    protected function mergeIcon(Machine $destination, Machine $source): void
    {
        if ($source->getIconHash() !== '') {
            $destination->setIconHash($source->getIconHash());
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
