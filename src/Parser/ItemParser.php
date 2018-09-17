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
     * The parsed items.
     * @var array|Item[]
     */
    protected $parsedItems = [];

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
     * Parses the data from the dump into actual entities.
     * @param DataContainer $dumpData
     */
    public function parse(DataContainer $dumpData): void
    {
        $this->parsedItems = [];
        foreach ($dumpData->getObjectArray('items') as $itemData) {
            $item = $this->parseItem($itemData, 'item');
            $this->parsedItems[$item->getIdentifier()] = $item;
        }
        foreach ($dumpData->getObjectArray('fluids') as $itemData) {
            $item = $this->parseItem($itemData, 'fluid');
            $this->parsedItems[$item->getIdentifier()] = $item;
        }
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

        $this->addTranslations($item, $itemData);
        return $item;
    }

    /**
     * Adds the translation to the item.
     * @param Item $item
     * @param DataContainer $itemData
     */
    protected function addTranslations(Item $item, DataContainer $itemData): void
    {
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
    }

    /**
     * Checks the parsed data.
     */
    public function check(): void
    {
        foreach ($this->parsedItems as $item) {
            $this->checkIcon($item);
        }
    }

    /**
     * Checks the icon of the item.
     * @param Item $item
     */
    protected function checkIcon(Item $item): void
    {
        $iconHash = $this->iconParser->getIconHashForEntity($item->getType(), $item->getName());
        if ($iconHash !== null) {
            $item->setIconHash($iconHash);
        }
    }

    /**
     * Persists the parsed data into the combination.
     * @param Combination $combination
     */
    public function persist(Combination $combination): void
    {
        $itemHashes = [];
        foreach ($this->parsedItems as $item) {
            $itemHashes[] = $this->itemRegistry->set($item);
        }
        $combination->setItemHashes($itemHashes);
    }
}
