<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Mapper;

use BluePsyduck\MapperManager\Mapper\StaticMapperInterface;
use BluePsyduck\MapperManager\MapperManagerAwareInterface;
use BluePsyduck\MapperManager\MapperManagerAwareTrait;
use FactorioItemBrowser\Export\Entity\Dump\Icon as DumpIcon;
use FactorioItemBrowser\ExportData\Entity\Icon as ExportIcon;
use FactorioItemBrowser\ExportData\Entity\Icon\Layer as ExportLayer;

/**
 * The mapper for the icons.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 *
 * @implements StaticMapperInterface<DumpIcon, ExportIcon>
 */
class IconMapper implements StaticMapperInterface, MapperManagerAwareInterface
{
    use MapperManagerAwareTrait;

    private const RENDERED_ICON_SIZE = 64;

    public function getSupportedSourceClass(): string
    {
        return DumpIcon::class;
    }

    public function getSupportedDestinationClass(): string
    {
        return ExportIcon::class;
    }

    /**
     * @param DumpIcon $source
     * @param ExportIcon $destination
     */
    public function map(object $source, object $destination): void
    {
        $destination->size = self::RENDERED_ICON_SIZE;
        foreach ($source->layers as $layer) {
            $destination->layers[] = $this->mapperManager->map($layer, new ExportLayer());
        }
    }
}
