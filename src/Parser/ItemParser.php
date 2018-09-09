<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Parser;

use BluePsyduck\Common\Data\DataContainer;
use FactorioItemBrowser\Export\I18n\Translator;
use FactorioItemBrowser\ExportData\Entity\Item;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;
use FactorioItemBrowser\ExportData\Registry\EntityRegistry;

/**
 * The class parsing the items of the dump.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ItemParser implements ParserInterface
{
    /**
     * The icon parser.
     * @var IconParser
     */
    protected $iconParser;

    /**
     * The item registry.
     * @var EntityRegistry
     */
    protected $itemRegistry;

    /**
     * The translator.
     * @var Translator
     */
    protected $translator;

    /**
     * Initializes the parser.
     * @param IconParser $iconParser
     * @param EntityRegistry $itemRegistry
     * @param Translator $translator
     */
    public function __construct(IconParser $iconParser, EntityRegistry $itemRegistry, Translator $translator)
    {
        $this->iconParser = $iconParser;
        $this->itemRegistry = $itemRegistry;
        $this->translator = $translator;
    }

    /**
     * Parses the dump data into the combination.
     * @param Combination $combination
     * @param DataContainer $dumpData
     */
    public function parse(Combination $combination, DataContainer $dumpData): void
    {
        foreach ($dumpData->getObjectArray('items') as $itemData) {
            $this->processItem($combination, $itemData, 'item');
        }
        foreach ($dumpData->getObjectArray('fluids') as $itemData) {
            $this->processItem($combination, $itemData, 'fluid');
        }
    }

    /**
     * Processes the specified item data.
     * @param Combination $combination
     * @param DataContainer $itemData
     * @param string $type
     */
    protected function processItem(Combination $combination, DataContainer $itemData, string $type): void
    {
        $item = $this->parseItem($itemData, $type);
        $this->assignIconHash($combination, $item);
        $combination->addItemHash($this->itemRegistry->set($item));
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
             ->setName(strtolower($itemData->getString('name')));

        $this->translator->addTranslationsToEntity(
            $item->getLabels(),
            'name',
            $itemData->get(['localised', 'name']),
            $itemData->get(['localised', 'entityName'])
        );
        $this->translator->addTranslationsToEntity(
            $item->getDescriptions(),
            'description',
            $itemData->get(['localised', 'description']),
            $itemData->get(['localised', 'entityDescription'])
        );
        return $item;
    }

    /**
     * Assigns the icon hash to the specified item.
     * @param Combination $combination
     * @param Item $item
     */
    protected function assignIconHash(Combination $combination, Item $item): void
    {
        $iconHash = $this->iconParser->getIconHashForEntity($combination, $item->getType(), $item->getName());
        if ($iconHash !== null) {
            $item->setIconHash($iconHash);
        }
    }
}
