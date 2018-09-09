<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Parser;

use BluePsyduck\Common\Data\DataContainer;
use FactorioItemBrowser\ExportData\Entity\Icon;
use FactorioItemBrowser\ExportData\Entity\Icon\Layer;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;
use FactorioItemBrowser\ExportData\Registry\EntityRegistry;

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
    protected $icons;

    /**
     * Initializes the parser.
     * @param EntityRegistry $iconRegistry
     */
    public function __construct(EntityRegistry $iconRegistry)
    {
        $this->iconRegistry = $iconRegistry;
    }

    /**
     * Parses the dump data into the combination.
     * @param Combination $combination
     * @param DataContainer $dumpData
     */
    public function parse(Combination $combination, DataContainer $dumpData): void
    {
        foreach ($dumpData->getObjectArray('icons') as $iconData) {
            $icon = $this->parseIcon($iconData);

            $name = $iconData->getString('name');
            $type = $iconData->getString('type');
            switch ($type) {
                case 'fluid':
                case 'item':
                case 'recipe':
                    $this->icons[$this->buildArrayKey($type, $name)] = $icon;
                    break;

                default:
                    $this->icons[$this->buildArrayKey('item', $name)] = $icon;
                    $this->icons[$this->buildArrayKey('machine', $name)] = $icon;
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
        $icon->setSize($iconData->getInteger('iconSize', Icon::DEFAULT_SIZE));
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
     * Returns the icon hash for the specified entity, if available.
     * @param Combination $combination
     * @param string $type
     * @param string $name
     * @return string|null
     */
    public function getIconHashForEntity(Combination $combination, string $type, string $name): ?string
    {
        $result = null;
        $key = $this->buildArrayKey($type, $name);
        if (isset($this->icons[$key])) {
            $result = $this->iconRegistry->set($this->icons[$key]);
            $combination->addIconHash($result);
        }
        return $result;
    }

    /**
     * Returns the key used in the array.
     * @param string $type
     * @param string $name
     * @return string
     */
    protected function buildArrayKey(string $type, string $name): string
    {
        return $type . '|' . $name;
    }
}
