<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Parser;

use BluePsyduck\Common\Data\DataContainer;
use FactorioItemBrowser\ExportData\Entity\Icon;
use FactorioItemBrowser\ExportData\Entity\Icon\Layer;
use FactorioItemBrowser\ExportData\Entity\Item;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;
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
     * @param Combination $combination
     * @param DataContainer $dumpData
     * @return $this
     */
    public function parse(Combination $combination, DataContainer $dumpData)
    {
        foreach ($dumpData->getObjectArray('icons') as $iconData) {
            $icon = $this->parseIcon($iconData);

            $name = $iconData->getString('name');
            $type = $iconData->getString('type');
            if ($type === 'recipe') {
                /* @var Recipe[] $recipes */
                $recipes = array_filter([
                    $combination->getRecipe($name, 'normal'),
                    $combination->getRecipe($name, 'expensive')
                ]);
                if (count($recipes) > 0) {
                    foreach ($recipes as $recipe) {
                        $recipe->setIconHash($icon->getIconHash());
                    }
                    if (!$combination->getIcon($icon->getIconHash()) instanceof Icon) {
                        $combination->addIcon($icon);
                    }
                }
            } else {
                $item = $combination->getItem($type, $name);
                if ($item instanceof Item) {
                    $item->setIconHash($icon->getIconHash());
                    if (!$combination->getIcon($icon->getIconHash()) instanceof Icon) {
                        $combination->addIcon($icon);
                    }
                }
            }
        }

        $this->addFallbackRecipeIcons($combination);
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
        $icon->setIconHash($this->calculateHash($icon));
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
        $layer
            ->setFileName($layerData->getString('icon'))
            ->setOffsetX($layerData->getInteger(['shift', 0], 0))
            ->setOffsetY($layerData->getInteger(['shift', 1], 0))
            ->setScale($layerData->getFloat('scale', 1.));

        $layer->getTintColor()
            ->setRed($layerData->getFloat(['tint', 'r'], 1.))
            ->setGreen($layerData->getFloat(['tint', 'g'], 1.))
            ->setBlue($layerData->getFloat(['tint', 'b'], 1.))
            ->setAlpha($layerData->getFloat(['tint', 'a'], 1.));
        return $layer;
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
        return hash('crc32b', json_encode($data));
    }

    /**
     * Adds a fallback icon to the recipes if they do not have one assigned already.
     * @param Combination $combination
     * @return $this
     */
    protected function addFallbackRecipeIcons(Combination $combination)
    {
        foreach ($combination->getRecipes() as $recipe) {
            if (strlen($recipe->getIconHash()) === 0) {
                $products = $recipe->getProducts();
                $firstProduct = reset($products);
                if ($firstProduct instanceof Product) {
                    $item = $combination->getItem($firstProduct->getType(), $firstProduct->getName());
                    if ($item instanceof Item) {
                        $recipe->setIconHash($item->getIconHash());
                    }
                }
            }
        }
        return $this;
    }
}