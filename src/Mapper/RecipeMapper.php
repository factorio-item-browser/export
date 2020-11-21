<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Mapper;

use BluePsyduck\MapperManager\Mapper\StaticMapperInterface;
use BluePsyduck\MapperManager\MapperManagerAwareInterface;
use BluePsyduck\MapperManager\MapperManagerAwareTrait;
use FactorioItemBrowser\Export\Entity\Dump\Recipe as DumpRecipe;
use FactorioItemBrowser\ExportData\Entity\Recipe as ExportRecipe;
use FactorioItemBrowser\ExportData\Entity\Recipe\Ingredient as ExportIngredient;
use FactorioItemBrowser\ExportData\Entity\Recipe\Product as ExportProduct;

/**
 * The mapper for the recipes.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 *
 * @implements StaticMapperInterface<DumpRecipe, ExportRecipe>
 */
class RecipeMapper implements StaticMapperInterface, MapperManagerAwareInterface
{
    use MapperManagerAwareTrait;

    public function getSupportedSourceClass(): string
    {
        return DumpRecipe::class;
    }

    public function getSupportedDestinationClass(): string
    {
        return ExportRecipe::class;
    }

    /**
     * @param DumpRecipe $source
     * @param ExportRecipe $destination
     */
    public function map(object $source, object $destination): void
    {
        $destination->name = $source->name;
        $destination->craftingTime = $source->craftingTime;
        $destination->craftingCategory = $source->craftingCategory;
        foreach ($source->ingredients as $ingredient) {
            if ($ingredient->amount > 0) {
                $destination->ingredients[] = $this->mapperManager->map($ingredient, new ExportIngredient());
            }
        }
        foreach ($source->products as $product) {
            if (($product->amountMin + $product->amountMax) / 2 * $product->probability > 0) {
                $destination->products[] = $this->mapperManager->map($product, new ExportProduct());
            }
        }
    }
}
