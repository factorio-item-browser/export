<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Mapper;

use FactorioItemBrowser\Common\Constant\EntityType;
use FactorioItemBrowser\Export\Entity\Dump\Fluid as DumpFluid;
use FactorioItemBrowser\Export\Mapper\FluidMapper;
use FactorioItemBrowser\ExportData\Entity\Item as ExportItem;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the FluidMapper class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Mapper\FluidMapper
 */
class FluidMapperTest extends TestCase
{
    /**
     * @covers ::getSupportedDestinationClass
     * @covers ::getSupportedSourceClass
     */
    public function testGetSupportedClasses(): void
    {
        $mapper = new FluidMapper();

        $this->assertSame(DumpFluid::class, $mapper->getSupportedSourceClass());
        $this->assertSame(ExportItem::class, $mapper->getSupportedDestinationClass());
    }

    /**
     * @covers ::map
     */
    public function testMap(): void
    {
        $source = new DumpFluid();
        $source->name = 'abc';

        $expectedDestination = new ExportItem();
        $expectedDestination->type = EntityType::FLUID;
        $expectedDestination->name = 'abc';

        $destination = new ExportItem();

        $mapper = new FluidMapper();
        $mapper->map($source, $destination);

        $this->assertEquals($expectedDestination, $destination);
    }
}
