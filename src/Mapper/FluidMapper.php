<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Mapper;

use BluePsyduck\MapperManager\Mapper\StaticMapperInterface;
use FactorioItemBrowser\Common\Constant\EntityType;
use FactorioItemBrowser\Export\Entity\Dump\Fluid as DumpFluid;
use FactorioItemBrowser\ExportData\Entity\Item as ExportItem;

/**
 * The mapper for the fluids.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 *
 * @implements StaticMapperInterface<DumpFluid, ExportItem>
 */
class FluidMapper implements StaticMapperInterface
{
    public function getSupportedSourceClass(): string
    {
        return DumpFluid::class;
    }

    public function getSupportedDestinationClass(): string
    {
        return ExportItem::class;
    }

    /**
     * @param DumpFluid $source
     * @param ExportItem $destination
     */
    public function map(object $source, object $destination): void
    {
        $destination->type = EntityType::FLUID;
        $destination->name = $source->name;
    }
}
