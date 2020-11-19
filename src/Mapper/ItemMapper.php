<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Mapper;

use BluePsyduck\MapperManager\Mapper\StaticMapperInterface;
use FactorioItemBrowser\Common\Constant\EntityType;
use FactorioItemBrowser\Export\Entity\Dump\Item as DumpItem;
use FactorioItemBrowser\ExportData\Entity\Item as ExportItem;

/**
 * The mapper for the items.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 *
 * @implements StaticMapperInterface<DumpItem, ExportItem>
 */
class ItemMapper implements StaticMapperInterface
{
    public function getSupportedSourceClass(): string
    {
        return DumpItem::class;
    }

    public function getSupportedDestinationClass(): string
    {
        return ExportItem::class;
    }

    /**
     * @param DumpItem $source
     * @param ExportItem $destination
     */
    public function map(object $source, object $destination): void
    {
        $destination->type = EntityType::ITEM;
        $destination->name = $source->name;
    }
}
