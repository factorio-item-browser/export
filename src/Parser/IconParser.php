<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Parser;

use FactorioItemBrowser\Common\Constant\EntityType;
use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\Entity\Dump\Icon as DumpIcon;
use FactorioItemBrowser\Export\Entity\Dump\Layer as DumpLayer;
use FactorioItemBrowser\Export\Helper\HashCalculator;
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
     * The types which are blacklisted from the parser.
     */
    protected const BLACKLISTED_TYPES = [
        'technology',
        'tutorial',
    ];

    /**
     * The hash calculator.
     * @var HashCalculator
     */
    protected $hashCalculator;

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
     * @param HashCalculator $hashCalculator
     */
    public function __construct(HashCalculator $hashCalculator)
    {
        $this->hashCalculator = $hashCalculator;
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
            if ($this->isIconValid($dumpIcon)) {
                $this->addParsedIcon($dumpIcon->getType(), strtolower($dumpIcon->getName()), $this->mapIcon($dumpIcon));
            }
        }
    }

    /**
     * Returns whether the icon is valid and can be processed further.
     * @param DumpIcon $dumpIcon
     * @return bool
     */
    protected function isIconValid(DumpIcon $dumpIcon): bool
    {
        return !in_array($dumpIcon->getType(), self::BLACKLISTED_TYPES, true);
    }

    /**
     * Maps the dump icon to an export one.
     * @param DumpIcon $dumpIcon
     * @return ExportIcon
     */
    protected function mapIcon(DumpIcon $dumpIcon): ExportIcon
    {
        $exportIcon = new ExportIcon();

        $isFirstLayer = true;
        foreach ($dumpIcon->getLayers() as $dumpLayer) {
            $layer = $this->mapLayer($dumpLayer);
            $exportIcon->addLayer($layer);

            if ($isFirstLayer) {
                $scaledSize = (int) ($layer->getSize() * $layer->getScale());
                $exportIcon->setSize($scaledSize);
                $isFirstLayer = false;
            }
        }

        $exportIcon->setId($this->hashCalculator->hashIcon($exportIcon));
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
                    ->setScale($dumpLayer->getScale())
                    ->setSize($dumpLayer->getSize());
        $exportLayer->getOffset()->setX($dumpLayer->getShiftX())
                                 ->setY($dumpLayer->getShiftY());
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
     * Returns the icon id for the specified type and name, if available.
     * @param string $type
     * @param string $name
     * @return string
     */
    public function getIconId(string $type, string $name): string
    {
        $result = '';
        $icon = $this->parsedIcons[$type][$name] ?? null;
        if ($icon !== null) {
            $this->usedIcons[$icon->getId()] = $icon;
            $result = $icon->getId();
        }
        return $result;
    }
}
