<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Mapper;

use FactorioItemBrowser\Export\Entity\Dump\Ingredient as DumpIngredient;
use FactorioItemBrowser\Export\Mapper\RecipeIngredientMapper;
use FactorioItemBrowser\ExportData\Entity\Recipe\Ingredient as ExportIngredient;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the RecipeIngredientMapper class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Mapper\RecipeIngredientMapper
 */
class RecipeIngredientMapperTest extends TestCase
{
    /**
     * @covers ::getSupportedDestinationClass
     * @covers ::getSupportedSourceClass
     */
    public function testGetSupportedClasses(): void
    {
        $instance = new RecipeIngredientMapper();

        $this->assertSame(DumpIngredient::class, $instance->getSupportedSourceClass());
        $this->assertSame(ExportIngredient::class, $instance->getSupportedDestinationClass());
    }

    /**
     * @covers ::map
     */
    public function testMap(): void
    {
        $source = new DumpIngredient();
        $source->type = 'abc';
        $source->name = 'def';
        $source->amount = 13.37;

        $expectedDestination = new ExportIngredient();
        $expectedDestination->type = 'abc';
        $expectedDestination->name = 'def';
        $expectedDestination->amount = 13.37;

        $destination = new ExportIngredient();

        $instance = new RecipeIngredientMapper();
        $instance->map($source, $destination);

        $this->assertEquals($expectedDestination, $destination);
    }
}
