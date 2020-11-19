<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Mapper;

use BluePsyduck\MapperManager\Mapper\StaticMapperInterface;
use FactorioItemBrowser\Export\Entity\Dump\Product as DumpProduct;
use FactorioItemBrowser\ExportData\Entity\Recipe\Product as ExportProduct;

/**
 * The mapper for the recipe products.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 *
 * @implements StaticMapperInterface<DumpProduct, ExportProduct>
 */
class RecipeProductMapper implements StaticMapperInterface
{
    public function getSupportedSourceClass(): string
    {
        return DumpProduct::class;
    }

    public function getSupportedDestinationClass(): string
    {
        return ExportProduct::class;
    }

    /**
     * @param DumpProduct $source
     * @param ExportProduct $destination
     */
    public function map(object $source, object $destination): void
    {
        $destination->type = $source->type;
        $destination->name = $source->name;
        $destination->amountMin = $source->amountMin;
        $destination->amountMax = $source->amountMax;
    }
}
