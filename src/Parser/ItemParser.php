<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Parser;

use FactorioItemBrowser\Common\Constant\EntityType;
use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\Entity\Dump\Fluid as DumpFluid;
use FactorioItemBrowser\Export\Entity\Dump\Item as DumpItem;
use FactorioItemBrowser\ExportData\Entity\Combination;
use FactorioItemBrowser\ExportData\Entity\Item as ExportItem;

/**
 * The parser of the items and fluids.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ItemParser implements ParserInterface
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
        foreach ($dump->items as $dumpItem) {
            $combination->addItem($this->mapItem($dumpItem));
        }
        foreach ($dump->fluids as $dumpFluid) {
            $combination->addItem($this->mapFluid($dumpFluid));
        }
    }

    protected function mapItem(DumpItem $dumpItem): ExportItem
    {
        $exportItem = new ExportItem();
        $exportItem->setType(EntityType::ITEM)
                   ->setName($dumpItem->name)
                   ->setIconId($this->iconParser->getIconId(EntityType::ITEM, $dumpItem->name));

        $this->translationParser->translate(
            $exportItem->getLabels(),
            $dumpItem->localisedName,
            $dumpItem->localisedEntityName,
        );
        $this->translationParser->translate(
            $exportItem->getDescriptions(),
            $dumpItem->localisedDescription,
            $dumpItem->localisedEntityDescription,
        );

        return $exportItem;
    }

    protected function mapFluid(DumpFluid $dumpFluid): ExportItem
    {
        $exportItem = new ExportItem();
        $exportItem->setType(EntityType::FLUID)
                   ->setName($dumpFluid->name)
                   ->setIconId($this->iconParser->getIconId(EntityType::FLUID, $dumpFluid->name));

        $this->translationParser->translate($exportItem->getLabels(), $dumpFluid->localisedName);
        $this->translationParser->translate($exportItem->getDescriptions(), $dumpFluid->localisedDescription);

        return $exportItem;
    }

    public function validate(Combination $combination): void
    {
    }
}
