<?php

namespace FactorioItemBrowserTest\Export\Reducer;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Export\Exception\ReducerException;
use FactorioItemBrowser\Export\Reducer\RecipeReducer;
use FactorioItemBrowser\ExportData\Entity\EntityInterface;
use FactorioItemBrowser\ExportData\Entity\Item;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;
use FactorioItemBrowser\ExportData\Entity\Recipe;
use FactorioItemBrowser\ExportData\Entity\Recipe\Ingredient;
use FactorioItemBrowser\ExportData\Entity\Recipe\Product;
use FactorioItemBrowser\ExportData\Registry\EntityRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the RecipeReducer class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Reducer\RecipeReducer
 */
class RecipeReducerTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests the getHashesFromCombination method.
     * @throws ReflectionException
     * @covers ::getHashesFromCombination
     */
    public function testGetHashesFromCombination(): void
    {
        $hashes = ['abc', 'def'];

        /* @var Combination|MockObject $combination */
        $combination = $this->getMockBuilder(Combination::class)
                            ->setMethods(['getRecipeHashes'])
                            ->disableOriginalConstructor()
                            ->getMock();
        $combination->expects($this->once())
                    ->method('getRecipeHashes')
                    ->willReturn($hashes);

        /* @var EntityRegistry $rawRecipeRegistry */
        $rawRecipeRegistry = $this->createMock(EntityRegistry::class);
        /* @var EntityRegistry $reducedRecipeRegistry */
        $reducedRecipeRegistry = $this->createMock(EntityRegistry::class);

        $reducer = new RecipeReducer($rawRecipeRegistry, $reducedRecipeRegistry);

        $result = $this->invokeMethod($reducer, 'getHashesFromCombination', $combination);
        $this->assertSame($hashes, $result);
    }

    /**
     * Provides the data for the reduceEntity test.
     * @return array
     */
    public function provideReduceEntity(): array
    {
        return [
            [new Recipe(), new Recipe(), false],
            [new Recipe(), new Item(), true],
            [new Item(), new Recipe(), true],
        ];
    }

    /**
     * Tests the reduceEntity method.
     * @param EntityInterface $entity
     * @param EntityInterface $parentEntity
     * @param bool $expectException
     * @throws ReflectionException
     * @covers ::reduceEntity
     * @dataProvider provideReduceEntity
     */
    public function testReduceEntity(
        EntityInterface $entity,
        EntityInterface $parentEntity,
        bool $expectException
    ): void {
        /* @var RecipeReducer|MockObject $reducer */
        $reducer = $this->getMockBuilder(RecipeReducer::class)
                        ->setMethods(['reduceData', 'reduceTranslations', 'reduceIcon'])
                        ->disableOriginalConstructor()
                        ->getMock();
        $reducer->expects($expectException ? $this->never() : $this->once())
                ->method('reduceData')
                ->with($entity, $parentEntity);
        $reducer->expects($expectException ? $this->never() : $this->once())
                ->method('reduceTranslations')
                ->with($entity, $parentEntity);
        $reducer->expects($expectException ? $this->never() : $this->once())
                ->method('reduceIcon')
                ->with($entity, $parentEntity);

        if ($expectException) {
            $this->expectException(ReducerException::class);
        }

        $this->invokeMethod($reducer, 'reduceEntity', $entity, $parentEntity);
    }

    /**
     * Provides the data for the reduceData test.
     * @return array
     */
    public function provideReduceData(): array
    {
        return [
            ['abc', 'abc', true],
            ['abc', 'def', false],
        ];
    }

    /**
     * Tests the reduceData method.
     * @param string $hash
     * @param string $parentHash
     * @param bool $expectReduction
     * @throws ReflectionException
     * @covers ::reduceData
     * @dataProvider provideReduceData
     */
    public function testReduceData(string $hash, string $parentHash, bool $expectReduction): void
    {
        $recipe = new Recipe();
        $recipe->setName('foo')
               ->addIngredient(new Ingredient())
               ->addProduct(new Product())
               ->setCraftingTime(13.37)
               ->setCraftingCategory('abc');

        $parentRecipe = (new Recipe())->setName('bar');
        $expectedRecipe = $expectReduction ? (new Recipe())->setName('foo') : $recipe;

        /* @var RecipeReducer|MockObject $reducer */
        $reducer = $this->getMockBuilder(RecipeReducer::class)
                        ->setMethods(['calculateDataHash'])
                        ->disableOriginalConstructor()
                        ->getMock();
        $reducer->expects($this->exactly(2))
                ->method('calculateDataHash')
                ->withConsecutive(
                    [$recipe],
                    [$parentRecipe]
                )
                ->willReturnOnConsecutiveCalls(
                    $hash,
                    $parentHash
                );

        $this->invokeMethod($reducer, 'reduceData', $recipe, $parentRecipe);
        $this->assertEquals($expectedRecipe, $recipe);
    }

    /**
     * Tests the calculateDataHash method.
     * @throws ReflectionException
     * @covers ::calculateDataHash
     */
    public function testCalculateDataHash(): void
    {
        $recipe = new Recipe();
        $recipe->setName('foo')
               ->addIngredient(new Ingredient())
               ->addProduct(new Product())
               ->setCraftingTime(13.37)
               ->setCraftingCategory('abc');

        $expectedResult = '7f374023f24e486c';

        /* @var EntityRegistry $rawRecipeRegistry */
        $rawRecipeRegistry = $this->createMock(EntityRegistry::class);
        /* @var EntityRegistry $reducedRecipeRegistry */
        $reducedRecipeRegistry = $this->createMock(EntityRegistry::class);

        $reducer = new RecipeReducer($rawRecipeRegistry, $reducedRecipeRegistry);

        $result = $this->invokeMethod($reducer, 'calculateDataHash', $recipe);
        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the reduceTranslations method.
     * @throws ReflectionException
     * @covers ::reduceTranslations
     */
    public function testReduceTranslations(): void
    {
        $recipe = new Recipe();
        $recipe->getLabels()->setTranslation('en', 'abc')
                ->setTranslation('de', 'def');
        $recipe->getDescriptions()->setTranslation('en', 'ghi')
                ->setTranslation('de', 'jkl');

        $parentRecipe = new Recipe();
        $parentRecipe->getLabels()->setTranslation('en', 'abc')
                      ->setTranslation('de', 'mno');
        $parentRecipe->getDescriptions()->setTranslation('en', 'ghi')
                      ->setTranslation('de', 'pqr');

        $expectedRecipe = new Recipe();
        $expectedRecipe->getLabels()->setTranslation('de', 'def');
        $expectedRecipe->getDescriptions()->setTranslation('de', 'jkl');

        /* @var EntityRegistry $rawRecipeRegistry */
        $rawRecipeRegistry = $this->createMock(EntityRegistry::class);
        /* @var EntityRegistry $reducedRecipeRegistry */
        $reducedRecipeRegistry = $this->createMock(EntityRegistry::class);

        $reducer = new RecipeReducer($rawRecipeRegistry, $reducedRecipeRegistry);

        $this->invokeMethod($reducer, 'reduceTranslations', $recipe, $parentRecipe);
        $this->assertEquals($expectedRecipe, $recipe);
    }

    /**
     * Provides the data for the reduceIcon test.
     * @return array
     */
    public function provideReduceIcon(): array
    {
        return [
            ['abc', 'abc', true],
            ['abc', 'def', false],
        ];
    }

    /**
     * Tests the reduceIcon method.
     * @param string $iconHash
     * @param string $parentIconHash
     * @param bool $expectSet
     * @throws ReflectionException
     * @covers ::reduceIcon
     * @dataProvider provideReduceIcon
     */
    public function testReduceIcon(string $iconHash, string $parentIconHash, bool $expectSet): void
    {
        /* @var Recipe|MockObject $recipe */
        $recipe = $this->getMockBuilder(Recipe::class)
                       ->setMethods(['getIconHash', 'setIconHash'])
                       ->disableOriginalConstructor()
                       ->getMock();
        $recipe->expects($this->once())
               ->method('getIconHash')
               ->willReturn($iconHash);
        $recipe->expects($expectSet ? $this->once() : $this->never())
               ->method('setIconHash')
               ->with('');

        /* @var Recipe|MockObject $parentRecipe */
        $parentRecipe = $this->getMockBuilder(Recipe::class)
                             ->setMethods(['getIconHash'])
                             ->disableOriginalConstructor()
                             ->getMock();
        $parentRecipe->expects($this->once())
                     ->method('getIconHash')
                     ->willReturn($parentIconHash);

        /* @var EntityRegistry $rawRecipeRegistry */
        $rawRecipeRegistry = $this->createMock(EntityRegistry::class);
        /* @var EntityRegistry $reducedRecipeRegistry */
        $reducedRecipeRegistry = $this->createMock(EntityRegistry::class);

        $reducer = new RecipeReducer($rawRecipeRegistry, $reducedRecipeRegistry);

        $this->invokeMethod($reducer, 'reduceIcon', $recipe, $parentRecipe);
    }

    /**
     * Provides the data for the isEntityEmpty test.
     * @return array
     */
    public function provideIsEntityEmpty(): array
    {
        $entity1 = new Recipe();

        $entity2 = new Recipe();
        $entity2->addIngredient(new Ingredient());

        $entity3 = new Recipe();
        $entity3->addProduct(new Product());

        $entity4 = new Recipe();
        $entity4->setIconHash('abc');

        $entity5 = new Recipe();
        $entity5->getLabels()->setTranslation('en', 'abc');

        $entity6 = new Recipe();
        $entity6->getDescriptions()->setTranslation('de', 'def');

        return [
            [$entity1, false, true],
            [$entity2, false, false],
            [$entity3, false, false],
            [$entity4, false, false],
            [$entity5, false, false],
            [$entity6, false, false],
            [new Item(), true, false],
        ];
    }

    /**
     * Tests the isEntityEmpty method.
     * @param EntityInterface $entity
     * @param bool $expectException
     * @param bool $expectedResult
     * @throws ReflectionException
     * @covers ::isEntityEmpty
     * @dataProvider provideIsEntityEmpty
     */
    public function testIsEntityEmpty(EntityInterface $entity, bool $expectException, bool $expectedResult): void
    {
        if ($expectException) {
            $this->expectException(ReducerException::class);
        }

        /* @var EntityRegistry $rawRecipeRegistry */
        $rawRecipeRegistry = $this->createMock(EntityRegistry::class);
        /* @var EntityRegistry $reducedRecipeRegistry */
        $reducedRecipeRegistry = $this->createMock(EntityRegistry::class);

        $reducer = new RecipeReducer($rawRecipeRegistry, $reducedRecipeRegistry);
        $result = $this->invokeMethod($reducer, 'isEntityEmpty', $entity);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the setHashesToCombination method.
     * @throws ReflectionException
     * @covers ::setHashesToCombination
     */
    public function testSetHashesToCombination(): void
    {
        $hashes = ['abc', 'def'];

        /* @var Combination|MockObject $combination */
        $combination = $this->getMockBuilder(Combination::class)
                            ->setMethods(['setRecipeHashes'])
                            ->disableOriginalConstructor()
                            ->getMock();
        $combination->expects($this->once())
                    ->method('setRecipeHashes')
                    ->with($hashes);

        /* @var EntityRegistry $rawRecipeRegistry */
        $rawRecipeRegistry = $this->createMock(EntityRegistry::class);
        /* @var EntityRegistry $reducedRecipeRegistry */
        $reducedRecipeRegistry = $this->createMock(EntityRegistry::class);

        $reducer = new RecipeReducer($rawRecipeRegistry, $reducedRecipeRegistry);

        $this->invokeMethod($reducer, 'setHashesToCombination', $combination, $hashes);
    }
}
