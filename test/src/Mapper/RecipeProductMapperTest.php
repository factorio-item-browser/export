<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Mapper;

use FactorioItemBrowser\Export\Entity\Dump\Product as DumpProduct;
use FactorioItemBrowser\Export\Mapper\RecipeProductMapper;
use FactorioItemBrowser\ExportData\Entity\Recipe\Product as ExportProduct;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the RecipeProductMapper class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Mapper\RecipeProductMapper
 */
class RecipeProductMapperTest extends TestCase
{
    /**
     * @covers ::getSupportedDestinationClass
     * @covers ::getSupportedSourceClass
     */
    public function testGetSupportedClasses(): void
    {
        $instance = new RecipeProductMapper();

        $this->assertSame(DumpProduct::class, $instance->getSupportedSourceClass());
        $this->assertSame(ExportProduct::class, $instance->getSupportedDestinationClass());
    }

    /**
     * @covers ::map
     */
    public function testMap(): void
    {
        $source = new DumpProduct();
        $source->type = 'abc';
        $source->name = 'def';
        $source->amountMin = 12.34;
        $source->amountMax = 34.56;
        $source->probability = 56.78;

        $expectedDestination = new ExportProduct();
        $expectedDestination->type = 'abc';
        $expectedDestination->name = 'def';
        $expectedDestination->amountMin = 12.34;
        $expectedDestination->amountMax = 34.56;
        $expectedDestination->probability = 56.78;

        $destination = new ExportProduct();

        $instance = new RecipeProductMapper();
        $instance->map($source, $destination);

        $this->assertEquals($expectedDestination, $destination);
    }
}
