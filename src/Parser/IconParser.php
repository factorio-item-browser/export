<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Parser;

use FactorioItemBrowser\Common\Constant\EntityType;
use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\Entity\Dump\Icon as DumpIcon;
use FactorioItemBrowser\Export\Entity\Dump\Layer as DumpLayer;
use FactorioItemBrowser\Export\Helper\HashingHelper;
use FactorioItemBrowser\ExportData\Entity\Combination;
use FactorioItemBrowser\ExportData\Entity\Icon as ExportIcon;
use FactorioItemBrowser\ExportData\Entity\Icon\Layer as ExportLayer;

/**
 * The parser of the icons.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class IconParser implements ParserInterface
{
    /**
     * The rendered size to use for the icons.
     */
    protected const RENDERED_SIZE = 32;

    /**
     * The types which are blacklisted from the parser.
     */
    protected const BLACKLISTED_TYPES = [
        'technology',
        'tutorial',
    ];

    /**
     * The hashing helper.
     * @var HashingHelper
     */
    protected $hashingHelper;

    /**
     * The parsed icons.
     * @var array|ExportIcon[][]
     */
    protected $parsedIcons = [];

    /**
     * The icons which are used by any entity.
     * @var array|ExportIcon[]
     */
    protected $usedIcons = [];

    /**
     * Initializes the parser.
     * @param HashingHelper $hashingHelper
     */
    public function __construct(HashingHelper $hashingHelper)
    {
        $this->hashingHelper = $hashingHelper;
    }

    /**
     * Prepares the parser to be able to later parse the dump.
     * @param Dump $dump
     */
    public function prepare(Dump $dump): void
    {
        $this->parsedIcons = [];
        $this->usedIcons = [];

        foreach ($dump->getDataStage()->getIcons() as $dumpIcon) {
            if (in_array($dumpIcon->getType(), self::BLACKLISTED_TYPES, true)) {
                continue;
            }

            $this->addParsedIcon($dumpIcon->getType(), strtolower($dumpIcon->getName()), $this->mapIcon($dumpIcon));
        }
    }

    /**
     * Maps the dump icon to an export one.
     * @param DumpIcon $dumpIcon
     * @return ExportIcon
     */
    protected function mapIcon(DumpIcon $dumpIcon): ExportIcon
    {
        // @todo Icon size seems to be sometimes at layer level, not at top level. Dump mod may need adjustments.

        $exportIcon = new ExportIcon();
        $exportIcon->setSize($dumpIcon->getSize())
                   ->setRenderedSize(self::RENDERED_SIZE);

        foreach ($dumpIcon->getLayers() as $dumpLayer) {
            $exportIcon->addLayer($this->mapLayer($dumpLayer));
        }

        $exportIcon->setHash($this->hashingHelper->hashIcon($exportIcon));
        return $exportIcon;
    }

    /**
     * Maps the dump layer to an export one.
     * @param DumpLayer $dumpLayer
     * @return ExportLayer
     */
    protected function mapLayer(DumpLayer $dumpLayer): ExportLayer
    {
        $exportLayer = new ExportLayer();
        $exportLayer->setFileName($dumpLayer->getFile())
                    ->setOffsetX($dumpLayer->getShiftX())
                    ->setOffsetY($dumpLayer->getShiftY())
                    ->setScale($dumpLayer->getScale());
        $exportLayer->getTint()->setRed($this->convertColorValue($dumpLayer->getTintRed()))
                               ->setGreen($this->convertColorValue($dumpLayer->getTintGreen()))
                               ->setBlue($this->convertColorValue($dumpLayer->getTintBlue()))
                               ->setAlpha($this->convertColorValue($dumpLayer->getTintAlpha()));
        return $exportLayer;
    }

    /**
     * Converts the specified color value to the range between 0 and 1.
     * @param float $value
     * @return float
     */
    protected function convertColorValue(float $value): float
    {
        return ($value > 1) ? ($value / 255.) : $value;
    }

    /**
     * Adds a parsed icon to the property array.
     * @param string $type
     * @param string $name
     * @param ExportIcon $icon
     */
    protected function addParsedIcon(string $type, string $name, ExportIcon $icon): void
    {
        switch ($type) {
            case EntityType::FLUID:
            case EntityType::ITEM:
            case EntityType::RECIPE:
                $this->parsedIcons[$type][$name] = $icon;
                break;

            default:
                if (!isset($this->parsedIcons[EntityType::ITEM][$name])) {
                    $this->parsedIcons[EntityType::ITEM][$name] = $icon;
                }
                if (!isset($this->parsedIcons[EntityType::MACHINE][$name])) {
                    $this->parsedIcons[EntityType::MACHINE][$name] = $icon;
                }
                break;
        }
    }

    /**
     * Parses the data from the dump into the combination.
     * @param Dump $dump
     * @param Combination $combination
     */
    public function parse(Dump $dump, Combination $combination): void
    {
    }

    /**
     * Validates the data in the combination as a second parsing step.
     * @param Combination $combination
     */
    public function validate(Combination $combination): void
    {
        $combination->setIcons(array_merge($combination->getIcons(), array_values($this->usedIcons)));
    }

    /**
     * Returns the icon hash for the specified type and name, if available.
     * @param string $type
     * @param string $name
     * @return string
     */
    public function getIconHash(string $type, string $name): string
    {
        $result = '';
        $icon = $this->parsedIcons[$type][$name] ?? null;
        if ($icon !== null) {
            $this->usedIcons[$icon->getHash()] = $icon;
            $result = $icon->getHash();
        }
        return $result;
    }
}
