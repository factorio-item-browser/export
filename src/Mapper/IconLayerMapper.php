<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Mapper;

use BluePsyduck\MapperManager\Mapper\StaticMapperInterface;
use FactorioItemBrowser\Export\Entity\Dump\Layer as DumpLayer;
use FactorioItemBrowser\ExportData\Entity\Icon\Layer as ExportLayer;

/**
 * The mapper for the icon layers.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 *
 * @implements StaticMapperInterface<DumpLayer, ExportLayer>
 */
class IconLayerMapper implements StaticMapperInterface
{
    public function getSupportedSourceClass(): string
    {
        return DumpLayer::class;
    }

    public function getSupportedDestinationClass(): string
    {
        return ExportLayer::class;
    }

    /**
     * @param DumpLayer $source
     * @param ExportLayer $destination
     */
    public function map(object $source, object $destination): void
    {
        $destination->fileName = $source->file;
        $destination->scale = $source->scale;
        $destination->size = $source->size;
        $destination->offset->x = $source->shiftX;
        $destination->offset->y = $source->shiftY;
        $destination->tint->red = $this->convertColorValue($source->tintRed);
        $destination->tint->green = $this->convertColorValue($source->tintGreen);
        $destination->tint->blue = $this->convertColorValue($source->tintBlue);
        $destination->tint->alpha = $this->convertColorValue($source->tintAlpha);
    }

    private function convertColorValue(float $value): float
    {
        return ($value > 1) ? ($value / 255.) : $value;
    }
}
