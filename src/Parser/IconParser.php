<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Parser;

use BluePsyduck\Common\Data\DataContainer;
use FactorioItemBrowser\Common\Constant\EntityType;
use FactorioItemBrowser\Export\Constant\Config;
use FactorioItemBrowser\ExportData\Entity\Icon;
use FactorioItemBrowser\ExportData\Entity\Icon\Layer;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;
use FactorioItemBrowser\ExportData\Registry\EntityRegistry;
use FactorioItemBrowser\ExportData\Utils\EntityUtils;

/**
 * The class parsing the icons of the dump data.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class IconParser implements ParserInterface
{
    /**
     * The icon registry.
     * @var EntityRegistry
     */
    protected $iconRegistry;

    /**
     * The parsed icons.
     * @var array|Icon[]
     */
    protected $parsedIcons = [];

    /**
     * The icons which are used by any entity.
     * @var array|Icon[]
     */
    protected $usedIcons = [];

    /**
     * Initializes the parser.
     * @param EntityRegistry $iconRegistry
     */
    public function __construct(EntityRegistry $iconRegistry)
    {
        $this->iconRegistry = $iconRegistry;
    }

    /**
     * Resets any previously aggregated data.
     */
    public function reset(): void
    {
        $this->parsedIcons = [];
        $this->usedIcons = [];
    }

    /**
     * Parses the data from the dump into actual entities.
     * @param DataContainer $dumpData
     */
    public function parse(DataContainer $dumpData): void
    {
        foreach ($dumpData->getObjectArray('icons') as $iconData) {
            $icon = $this->parseIcon($iconData);

            $name = $iconData->getString('name');
            $type = $iconData->getString('type');

            switch ($type) {
                case EntityType::FLUID:
                case EntityType::ITEM:
                case EntityType::RECIPE:
                    $this->addParsedIcon($type, $name, $icon, true);
                    break;

                case 'technology':
                case 'tutorial':
                    // Types are blacklisted and must not provide any icons.
                    break;

                default:
                    $this->addParsedIcon(EntityType::ITEM, $name, $icon, false);
                    $this->addParsedIcon(EntityType::MACHINE, $name, $icon, false);
                    break;
            }
        }
    }

    /**
     * Parses the icon data to an entity.
     * @param DataContainer $iconData
     * @return Icon
     */
    protected function parseIcon(DataContainer $iconData): Icon
    {
        $icon = new Icon();
        foreach ($iconData->getObjectArray('icons') as $layerData) {
            $icon->addLayer($this->parseLayer($layerData));
        }
        $icon->setSize($iconData->getInteger('iconSize', Config::ICON_SIZE))
             ->setRenderedSize(Config::ICON_SIZE);
        return $icon;
    }

    /**
     * Parses the layer data to an entity.
     * @param DataContainer $layerData
     * @return Layer
     */
    protected function parseLayer(DataContainer $layerData): Layer
    {
        $layer = new Layer();
        $layer->setFileName($layerData->getString('icon'))
              ->setOffsetX($layerData->getInteger(['shift', '0'], 0))
              ->setOffsetY($layerData->getInteger(['shift', '1'], 0))
              ->setScale($layerData->getFloat('scale', 1.));

        $layer->getTintColor()->setRed($this->convertColorValue($layerData->getFloat(['tint', 'r'], 1.)))
                              ->setGreen($this->convertColorValue($layerData->getFloat(['tint', 'g'], 1.)))
                              ->setBlue($this->convertColorValue($layerData->getFloat(['tint', 'b'], 1.)))
                              ->setAlpha($this->convertColorValue($layerData->getFloat(['tint', 'a'], 1.)));
        return $layer;
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
     * Adds the specified icon to the list of parsed icons.
     * @param string $type
     * @param string $name
     * @param Icon $icon
     * @param bool $overwriteExistingIcon
     */
    protected function addParsedIcon(string $type, string $name, Icon $icon, bool $overwriteExistingIcon): void
    {
        $key = $this->buildArrayKey($type, $name);
        if (!isset($this->parsedIcons[$key]) || $overwriteExistingIcon) {
            $this->parsedIcons[$key] = $icon;
        }
    }

    /**
     * Checks the parsed data.
     */
    public function check(): void
    {
    }

    /**
     * Persists the parsed data into the combination.
     * @param Combination $combination
     */
    public function persist(Combination $combination): void
    {
        foreach ($this->usedIcons as $icon) {
            $this->iconRegistry->set($icon);
        }
        $combination->setIconHashes(array_keys($this->usedIcons));
    }

    /**
     * Returns the icon hash for the specified entity, if available.
     * @param string $type
     * @param string $name
     * @return string|null
     */
    public function getIconHashForEntity(string $type, string $name): ?string
    {
        $iconHash = null;
        $icon = $this->parsedIcons[$this->buildArrayKey($type, $name)] ?? null;
        if ($icon instanceof Icon) {
            $iconHash = $icon->calculateHash();
            $this->usedIcons[$iconHash] = $icon;
        }
        return $iconHash;
    }

    /**
     * Returns the key used in the array.
     * @param string $type
     * @param string $name
     * @return string
     */
    protected function buildArrayKey(string $type, string $name): string
    {
        return EntityUtils::buildIdentifier([$type, $name]);
    }
}
