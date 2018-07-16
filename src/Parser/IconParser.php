<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Parser;

use BluePsyduck\Common\Data\DataContainer;
use FactorioItemBrowser\ExportData\Entity\Icon;
use FactorioItemBrowser\ExportData\Entity\Icon\Layer;
use FactorioItemBrowser\ExportData\Entity\Item;
use FactorioItemBrowser\ExportData\Entity\Machine;
use FactorioItemBrowser\ExportData\Entity\Mod\CombinationData;
use FactorioItemBrowser\ExportData\Entity\Recipe;
use FactorioItemBrowser\ExportData\Entity\Recipe\Product;

/**
 * The class parsing the icons of the dump data.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class IconParser extends AbstractParser
{
    /**
     * Parses the dump data into the combination.
     * @param CombinationData $combinationData
     * @param DataContainer $dumpData
     * @return $this
     */
    public function parse(CombinationData $combinationData, DataContainer $dumpData)
    {
        foreach ($dumpData->getObjectArray('icons') as $iconData) {
            $icon = $this->parseIcon($iconData);

            $name = $iconData->getString('name');
            $type = $iconData->getString('type');
            switch ($type) {
                case 'recipe':
                    $this->processIcon($combinationData, $combinationData->getRecipe('normal', $name), $icon, true);
                    $this->processIcon($combinationData, $combinationData->getRecipe('expensive', $name), $icon, true);
                    break;

                case 'item':
                case 'fluid':
                    $this->processIcon($combinationData, $combinationData->getItem($type, $name), $icon, true);
                    break;

                default:
                    $this->processIcon($combinationData, $combinationData->getItem('item', $name), $icon, false);
                    $this->processIcon($combinationData, $combinationData->getMachine($name), $icon, false);
                    break;
            }
        }

        $this->addFallbackRecipeIcons($combinationData);
        return $this;
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
        $icon->setSize($iconData->getInteger('iconSize', Icon::DEFAULT_SIZE))
             ->setHash($this->calculateHash($icon));
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
              ->setOffsetX($layerData->getInteger(['shift', 0], 0))
              ->setOffsetY($layerData->getInteger(['shift', 1], 0))
              ->setScale($layerData->getFloat('scale', 1.));

        $layer->getTintColor()
              ->setRed($this->convertColorValue($layerData->getFloat(['tint', 'r'], 1.)))
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
     * Calculates the hash of the specified icon.
     * @param Icon $icon
     * @return string
     */
    protected function calculateHash(Icon $icon): string
    {
        $data = array_map(function(Layer $layer): array {
            return $layer->writeData();
        }, $icon->getLayers());
        $data[] = $icon->getSize();
        return hash('crc32b', json_encode($data));
    }

    /**
     * Processes the specified icon.
     * @param CombinationData $combinationData
     * @param Item|Recipe|Machine|null $entity
     * @param Icon $icon
     * @param bool $preferredMatch
     * @return $this
     */
    protected function processIcon(CombinationData $combinationData, $entity, Icon $icon, bool $preferredMatch)
    {
        if (!is_null($entity) && ($preferredMatch || strlen($entity->getIconHash()) === 0)) {
            $entity->setIconHash($icon->getHash());
            if (!$combinationData->getIcon($icon->getHash()) instanceof Icon) {
                $combinationData->addIcon($icon);
            }
        }
        return $this;
    }

    /**
     * Adds a fallback icon to the recipes if they do not have one assigned already.
     * @param CombinationData $combinationData
     * @return $this
     */
    protected function addFallbackRecipeIcons(CombinationData $combinationData)
    {
        foreach ($combinationData->getRecipes() as $recipe) {
            if (strlen($recipe->getIconHash()) === 0) {
                $products = $recipe->getProducts();
                $firstProduct = reset($products);
                if ($firstProduct instanceof Product) {
                    $item = $combinationData->getItem($firstProduct->getType(), $firstProduct->getName());
                    if ($item instanceof Item) {
                        $recipe->setIconHash($item->getIconHash());
                    }
                }
            }
        }
        return $this;
    }
}