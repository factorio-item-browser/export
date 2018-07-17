<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Parser;

use BluePsyduck\Common\Data\DataContainer;
use FactorioItemBrowser\ExportData\Entity\Item;
use FactorioItemBrowser\ExportData\Entity\Mod\CombinationData;

/**
 * The class parsing the items of the dump.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ItemParser extends AbstractParser
{
    /**
     * Parses the dump data into the combination.
     * @param CombinationData $combinationData
     * @param DataContainer $dumpData
     * @return $this
     */
    public function parse(CombinationData $combinationData, DataContainer $dumpData)
    {
        foreach ($dumpData->getObjectArray('items') as $itemData) {
            $combinationData->addItem($this->parseItem($itemData, 'item'));
        }
        foreach ($dumpData->getObjectArray('fluids') as $fluidData) {
            $combinationData->addItem($this->parseItem($fluidData, 'fluid'));
        }
        return $this;
    }

    /**
     * Parses the specified data into an item entity.
     * @param DataContainer $itemData
     * @param string $type
     * @return Item
     */
    protected function parseItem(DataContainer $itemData, string $type): Item
    {
        $item = new Item();
        $item->setType($type)
             ->setName($itemData->getString('name'));

        $this->translator->addTranslations(
            $item->getLabels(),
            'name',
            $itemData->get(['localised', 'name']),
            $itemData->get(['localised', 'entityName'])
        );
        $this->translator->addTranslations(
            $item->getDescriptions(),
            'description',
            $itemData->get(['localised', 'description']),
            $itemData->get(['localised', 'entityDescription'])
        );
        return $item;
    }
}
