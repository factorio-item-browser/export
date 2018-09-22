<?php

namespace FactorioItemBrowserTest\Export\Reducer;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Export\Exception\ReducerException;
use FactorioItemBrowser\Export\Reducer\RecipeReducer;
use FactorioItemBrowser\ExportData\Entity\EntityInterface;
use FactorioItemBrowser\ExportData\Entity\Item;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;
use FactorioItemBrowser\ExportData\Entity\Recipe;
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
                        ->setMethods(['reduceDataOfRecipe', 'reduceTranslationsOfRecipe', 'reduceIconOfRecipe'])
                        ->disableOriginalConstructor()
                        ->getMock();
        $reducer->expects($expectException ? $this->never() : $this->once())
                ->method('reduceDataOfRecipe')
                ->with($entity, $parentEntity);
        $reducer->expects($expectException ? $this->never() : $this->once())
                ->method('reduceTranslationsOfRecipe')
                ->with($entity, $parentEntity);
        $reducer->expects($expectException ? $this->never() : $this->once())
                ->method('reduceIconOfRecipe')
                ->with($entity, $parentEntity);

        if ($expectException) {
            $this->expectException(ReducerException::class);
        }

        $this->invokeMethod($reducer, 'reduceEntity', $entity, $parentEntity);
    }

    /**
     * Provides the data for the reduceIconOfRecipe test.
     * @return array
     */
    public function provideReduceIconOfRecipe(): array
    {
        return [
            ['abc', 'abc', true],
            ['abc', 'def', false],
        ];
    }

    /**
     * Tests the reduceIconOfRecipe method.
     * @param string $iconHash
     * @param string $parentIconHash
     * @param bool $expectSet
     * @throws ReflectionException
     * @covers ::reduceIconOfRecipe
     * @dataProvider provideReduceIconOfRecipe
     */
    public function testReduceIconOfRecipe(string $iconHash, string $parentIconHash, bool $expectSet): void
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

        $this->invokeMethod($reducer, 'reduceIconOfRecipe', $recipe, $parentRecipe);
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
