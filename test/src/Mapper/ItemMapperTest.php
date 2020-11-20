<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Mapper;

use FactorioItemBrowser\Common\Constant\EntityType;
use FactorioItemBrowser\Export\Entity\Dump\Item as DumpItem;
use FactorioItemBrowser\Export\Mapper\ItemMapper;
use FactorioItemBrowser\ExportData\Entity\Item as ExportItem;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the ItemMapper class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Mapper\ItemMapper
 */
class ItemMapperTest extends TestCase
{
    /**
     * @covers ::getSupportedDestinationClass
     * @covers ::getSupportedSourceClass
     */
    public function testGetSupportedClasses(): void
    {
        $mapper = new ItemMapper();

        $this->assertSame(DumpItem::class, $mapper->getSupportedSourceClass());
        $this->assertSame(ExportItem::class, $mapper->getSupportedDestinationClass());
    }

    /**
     * @covers ::map
     */
    public function testMap(): void
    {
        $source = new DumpItem();
        $source->name = 'abc';

        $expectedDestination = new ExportItem();
        $expectedDestination->type = EntityType::ITEM;
        $expectedDestination->name = 'abc';

        $destination = new ExportItem();

        $mapper = new ItemMapper();
        $mapper->map($source, $destination);

        $this->assertEquals($expectedDestination, $destination);
    }
}
