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
    protected const RENDERED_ICON_SIZE = 64;
    protected const BLACKLISTED_TYPES = [
        'technology',
        'tutorial',
    ];

    protected HashCalculator $hashCalculator;

    /** @var array<string,array<string,ExportIcon>> */
    protected array $parsedIcons = [];
    /** @var array<string,ExportIcon> */
    protected $usedIcons = [];

    public function __construct(HashCalculator $hashCalculator)
    {
        $this->hashCalculator = $hashCalculator;
    }

    public function prepare(Dump $dump): void
    {
        $this->parsedIcons = [];
        $this->usedIcons = [];

        foreach ($dump->icons as $dumpIcon) {
            if ($this->isIconValid($dumpIcon)) {
                $this->addParsedIcon($dumpIcon->type, $dumpIcon->name, $this->mapIcon($dumpIcon));
            }
        }
    }

    protected function isIconValid(DumpIcon $dumpIcon): bool
    {
        return !in_array($dumpIcon->type, self::BLACKLISTED_TYPES, true);
    }

    protected function mapIcon(DumpIcon $dumpIcon): ExportIcon
    {
        $exportIcon = new ExportIcon();
        $exportIcon->setSize(self::RENDERED_ICON_SIZE);

        foreach ($dumpIcon->layers as $dumpLayer) {
            $exportIcon->addLayer($this->mapLayer($dumpLayer));
        }

        $exportIcon->setId($this->hashCalculator->hashIcon($exportIcon));
        return $exportIcon;
    }

    protected function mapLayer(DumpLayer $dumpLayer): ExportLayer
    {
        $exportLayer = new ExportLayer();
        $exportLayer->setFileName($dumpLayer->file)
                    ->setScale($dumpLayer->scale)
                    ->setSize($dumpLayer->size);
        $exportLayer->getOffset()->setX($dumpLayer->shiftX)
                                 ->setY($dumpLayer->shiftY);
        $exportLayer->getTint()->setRed($this->convertColorValue($dumpLayer->tintRed))
                               ->setGreen($this->convertColorValue($dumpLayer->tintGreen))
                               ->setBlue($this->convertColorValue($dumpLayer->tintBlue))
                               ->setAlpha($this->convertColorValue($dumpLayer->tintAlpha));
        return $exportLayer;
    }

    protected function convertColorValue(float $value): float
    {
        return ($value > 1) ? ($value / 255.) : $value;
    }

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

    public function parse(Dump $dump, Combination $combination): void
    {
    }

    public function validate(Combination $combination): void
    {
        $combination->setIcons(array_merge($combination->getIcons(), array_values($this->usedIcons)));
    }

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
