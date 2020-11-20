<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Mapper;

use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Export\Entity\Dump\Ingredient as DumpIngredient;
use FactorioItemBrowser\Export\Entity\Dump\Product as DumpProduct;
use FactorioItemBrowser\Export\Mapper\RecipeMapper;
use FactorioItemBrowser\Export\Entity\Dump\Recipe as DumpRecipe;
use FactorioItemBrowser\ExportData\Entity\Recipe as ExportRecipe;
use FactorioItemBrowser\ExportData\Entity\Recipe\Ingredient as ExportIngredient;
use FactorioItemBrowser\ExportData\Entity\Recipe\Product as ExportProduct;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the RecipeMapper class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Mapper\RecipeMapper
 */
class RecipeMapperTest extends TestCase
{
    /**
     * @covers ::getSupportedDestinationClass
     * @covers ::getSupportedSourceClass
     */
    public function testGetSupportedClasses(): void
    {
        $instance = new RecipeMapper();

        $this->assertSame(DumpRecipe::class, $instance->getSupportedSourceClass());
        $this->assertSame(ExportRecipe::class, $instance->getSupportedDestinationClass());
    }

    /**
     * Tests the map method.
     * @covers ::map
     */
    public function testMap(): void
    {
        $sourceIngredient1 = new DumpIngredient();
        $sourceIngredient1->name = 'ghi';
        $sourceIngredient1->amount = 4.2;
        $sourceIngredient2 = new DumpIngredient();
        $sourceIngredient2->name = 'jkl';
        $sourceIngredient2->amount = 0;
        $sourceIngredient3 = new DumpIngredient();
        $sourceIngredient3->name = 'mno';
        $sourceIngredient3->amount = 2.1;

        $sourceProduct1 = new DumpProduct();
        $sourceProduct1->name = 'pqr';
        $sourceProduct1->amountMin = 4.2;
        $sourceProduct1->amountMax = 4.2;
        $sourceProduct2 = new DumpProduct();
        $sourceProduct2->name = 'stu';
        $sourceProduct2->amountMin = 2.1;
        $sourceProduct2->amountMax = 2.1;
        $sourceProduct2->probability = 0;
        $sourceProduct3 = new DumpProduct();
        $sourceProduct3->name = 'vwx';
        $sourceProduct2->amountMin = 2.1;
        $sourceProduct2->amountMax = 4.2;
        $sourceProduct3->probability = 0.5;

        $destinationIngredient1 = new ExportIngredient();
        $destinationIngredient1->name = 'ghi';
        $destinationIngredient3 = new ExportIngredient();
        $destinationIngredient3->name = 'mno';

        $destinationProduct1 = new ExportProduct();
        $destinationProduct1->name = 'pqr';
        $destinationProduct3 = new ExportProduct();
        $destinationProduct3->name = 'uvw';

        $source = new DumpRecipe();
        $source->name = 'abc';
        $source->craftingTime = 13.37;
        $source->craftingCategory = 'def';
        $source->ingredients = [$sourceIngredient1, $sourceIngredient2, $sourceIngredient3];
        $source->products = [$sourceProduct1, $sourceProduct2, $sourceProduct3];

        $expectedDestination = new ExportRecipe();
        $expectedDestination->name = 'abc';
        $expectedDestination->craftingTime = 13.37;
        $expectedDestination->craftingCategory = 'def';
        $expectedDestination->ingredients = [$destinationIngredient1, $destinationIngredient3];
        $expectedDestination->products = [$destinationProduct1, $destinationProduct3];

        $destination = new ExportRecipe();

        $mapperManager = $this->createMock(MapperManagerInterface::class);
        $mapperManager->expects($this->exactly(4))
                      ->method('map')
                      ->withConsecutive(
                          [$this->identicalTo($sourceIngredient1), $this->isInstanceOf(ExportIngredient::class)],
                          [$this->identicalTo($sourceIngredient3), $this->isInstanceOf(ExportIngredient::class)],
                          [$this->identicalTo($sourceProduct1), $this->isInstanceOf(ExportProduct::class)],
                          [$this->identicalTo($sourceProduct3), $this->isInstanceOf(ExportProduct::class)],
                      )
                      ->willReturnOnConsecutiveCalls(
                          $destinationIngredient1,
                          $destinationIngredient3,
                          $destinationProduct1,
                          $destinationProduct3,
                      );

        $instance = new RecipeMapper();
        $instance->setMapperManager($mapperManager);
        $instance->map($source, $destination);

        $this->assertEquals($expectedDestination, $destination);
    }
}
