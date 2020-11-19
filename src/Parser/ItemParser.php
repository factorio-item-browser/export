<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Parser;

use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Common\Constant\EntityType;
use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\Entity\Dump\Fluid as DumpFluid;
use FactorioItemBrowser\Export\Entity\Dump\Item as DumpItem;
use FactorioItemBrowser\ExportData\Entity\Item as ExportItem;
use FactorioItemBrowser\ExportData\ExportData;

/**
 * The parser of the items and fluids.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ItemParser implements ParserInterface
{
    protected IconParser $iconParser;
    protected MapperManagerInterface $mapperManager;
    protected TranslationParser $translationParser;

    public function __construct(
        IconParser $iconParser,
        MapperManagerInterface $mapperManager,
        TranslationParser $translationParser
    ) {
        $this->iconParser = $iconParser;
        $this->mapperManager = $mapperManager;
        $this->translationParser = $translationParser;
    }

    public function prepare(Dump $dump): void
    {
    }

    public function parse(Dump $dump, ExportData $exportData): void
    {
        foreach ($dump->items as $dumpItem) {
            $exportData->getItems()->add($this->createItem($dumpItem));
        }
        foreach ($dump->fluids as $dumpFluid) {
            $exportData->getItems()->add($this->createFluid($dumpFluid));
        }
    }

    protected function createItem(DumpItem $dumpItem): ExportItem
    {
        $exportItem = $this->mapperManager->map($dumpItem, new ExportItem());
        $exportItem->iconId = $this->iconParser->getIconId(EntityType::ITEM, $dumpItem->name);

        $this->translationParser->translate(
            $exportItem->labels,
            $dumpItem->localisedName,
            $dumpItem->localisedEntityName,
        );
        $this->translationParser->translate(
            $exportItem->descriptions,
            $dumpItem->localisedDescription,
            $dumpItem->localisedEntityDescription,
        );

        return $exportItem;
    }

    protected function createFluid(DumpFluid $dumpFluid): ExportItem
    {
        $exportItem = $this->mapperManager->map($dumpFluid, new ExportItem());
        $exportItem->iconId = $this->iconParser->getIconId(EntityType::FLUID, $dumpFluid->name);

        $this->translationParser->translate($exportItem->labels, $dumpFluid->localisedName);
        $this->translationParser->translate($exportItem->descriptions, $dumpFluid->localisedDescription);

        return $exportItem;
    }

    public function validate(ExportData $exportData): void
    {
    }
}
