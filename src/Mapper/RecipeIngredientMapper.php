<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Mapper;

use BluePsyduck\MapperManager\Mapper\StaticMapperInterface;
use FactorioItemBrowser\Export\Entity\Dump\Ingredient as DumpIngredient;
use FactorioItemBrowser\ExportData\Entity\Recipe\Ingredient as ExportIngredient;

/**
 * The mapper for the recipe ingredients.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 *
 * @implements StaticMapperInterface<DumpIngredient, ExportIngredient>
 */
class RecipeIngredientMapper implements StaticMapperInterface
{
    public function getSupportedSourceClass(): string
    {
        return DumpIngredient::class;
    }

    public function getSupportedDestinationClass(): string
    {
        return ExportIngredient::class;
    }

    /**
     * @param DumpIngredient $source
     * @param ExportIngredient $destination
     */
    public function map(object $source, object $destination): void
    {
        $destination->type = $source->type;
        $destination->name = $source->name;
        $destination->amount = $source->amount;
    }
}
