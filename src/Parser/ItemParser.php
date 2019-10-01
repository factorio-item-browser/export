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
        foreach ($dump->getControlStage()->getItems() as $dumpItem) {
            $combination->addItem($this->mapItem($dumpItem));
        }
        foreach ($dump->getControlStage()->getFluids() as $dumpFluid) {
            $combination->addItem($this->mapFluid($dumpFluid));
        }
    }

    /**
     * Maps the dump item to an export one.
     * @param DumpItem $dumpItem
     * @return ExportItem
     */
    protected function mapItem(DumpItem $dumpItem): ExportItem
    {
        $exportItem = new ExportItem();
        $exportItem->setType(EntityType::ITEM)
                   ->setName(strtolower($dumpItem->getName()))
                   ->setIconHash($this->iconParser->getIconHash(EntityType::ITEM, strtolower($dumpItem->getName())));

        $this->translationParser->translateNames(
            $exportItem->getLabels(),
            $dumpItem->getLocalisedName(),
            $dumpItem->getLocalisedEntityName()
        );
        $this->translationParser->translateDescriptions(
            $exportItem->getDescriptions(),
            $dumpItem->getLocalisedDescription(),
            $dumpItem->getLocalisedEntityDescription()
        );

        return $exportItem;
    }

    /**
     * Maps the dump fluid to an export item.
     * @param DumpFluid $dumpFluid
     * @return ExportItem
     */
    protected function mapFluid(DumpFluid $dumpFluid): ExportItem
    {
        $exportItem = new ExportItem();
        $exportItem->setType(EntityType::FLUID)
                   ->setName(strtolower($dumpFluid->getName()))
                   ->setIconHash($this->iconParser->getIconHash(EntityType::FLUID, strtolower($dumpFluid->getName())));

        $this->translationParser->translateNames($exportItem->getLabels(), $dumpFluid->getLocalisedName());
        $this->translationParser->translateDescriptions(
            $exportItem->getDescriptions(),
            $dumpFluid->getLocalisedDescription()
        );

        return $exportItem;
    }

    /**
     * Validates the data in the combination as a second parsing step.
     * @param Combination $combination
     */
    public function validate(Combination $combination): void
    {
    }
}
