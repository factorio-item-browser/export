<?php

namespace FactorioItemBrowserTest\Export\Merger;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Export\Exception\MergerException;
use FactorioItemBrowser\Export\Merger\RecipeMerger;
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
 * The PHPUnit test of the RecipeMerger class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Merger\RecipeMerger
 */
class RecipeMergerTest extends TestCase
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

        /* @var EntityRegistry $recipeRegistry */
        $recipeRegistry = $this->createMock(EntityRegistry::class);

        $merger = new RecipeMerger($recipeRegistry);

        $result = $this->invokeMethod($merger, 'getHashesFromCombination', $combination);
        $this->assertSame($hashes, $result);
    }

    /**
     * Provides the data for the mergeEntity test.
     * @return array
     */
    public function provideMergeEntity(): array
    {
        return [
            [new Recipe(), new Recipe(), false],
            [new Recipe(), new Item(), true],
            [new Item(), new Recipe(), true],
        ];
    }

    /**
     * Tests the mergeEntity method.
     * @param EntityInterface $destination
     * @param EntityInterface $source
     * @param bool $expectException
     * @throws ReflectionException
     * @covers ::mergeEntity
     * @dataProvider provideMergeEntity
     */
    public function testMergeEntity(
        EntityInterface $destination,
        EntityInterface $source,
        bool $expectException
    ): void {
        /* @var RecipeMerger|MockObject $merger */
        $merger = $this->getMockBuilder(RecipeMerger::class)
                       ->setMethods(['mergeData', 'mergeTranslations', 'mergeIcon'])
                       ->disableOriginalConstructor()
                       ->getMock();
        $merger->expects($expectException ? $this->never() : $this->once())
               ->method('mergeData')
               ->with($destination, $source);
        $merger->expects($expectException ? $this->never() : $this->once())
               ->method('mergeTranslations')
               ->with($destination, $source);
        $merger->expects($expectException ? $this->never() : $this->once())
               ->method('mergeIcon')
               ->with($destination, $source);

        if ($expectException) {
            $this->expectException(MergerException::class);
        }

        $this->invokeMethod($merger, 'mergeEntity', $destination, $source);
    }

    /**
     * Provides the data for the mergeData test.
     * @return array
     */
    public function provideMergeData(): array
    {
        $destination = new Recipe();
        $destination->setName('foo')
                    ->addIngredient((new Ingredient())->setName('abc'))
                    ->addProduct((new Product())->setName('def'))
                    ->setCraftingTime(13.37)
                    ->setCraftingCategory('ghi');

        $source1 = new Recipe();
        $source1->setName('bar')
                ->addIngredient((new Ingredient())->setName('jkl'))
                ->addProduct((new Product())->setName('mno'))
                ->setCraftingTime(13.37)
                ->setCraftingCategory('pqr');

        $source2 = new Recipe();
        $source2->setName('bar')
                ->addIngredient((new Ingredient())->setName('jkl'))
                ->setCraftingTime(13.37)
                ->setCraftingCategory('pqr');

        $source3 = new Recipe();
        $source3->setName('bar')
                ->addProduct((new Product())->setName('mno'))
                ->setCraftingTime(13.37)
                ->setCraftingCategory('pqr');

        $source4 = new Recipe();
        $source4->setName('bar')
                ->setCraftingTime(13.37)
                ->setCraftingCategory('pqr');

        $expectedDestination1 = new Recipe();
        $expectedDestination1->setName('foo')
                             ->addIngredient((new Ingredient())->setName('jkl'))
                             ->addProduct((new Product())->setName('mno'))
                             ->setCraftingTime(13.37)
                             ->setCraftingCategory('pqr');

        $expectedDestination2 = new Recipe();
        $expectedDestination2->setName('foo')
                             ->addIngredient((new Ingredient())->setName('jkl'))
                             ->setCraftingTime(13.37)
                             ->setCraftingCategory('pqr');

        $expectedDestination3 = new Recipe();
        $expectedDestination3->setName('foo')
                             ->addProduct((new Product())->setName('mno'))
                             ->setCraftingTime(13.37)
                             ->setCraftingCategory('pqr');


        return [
            [$destination, $source1, $expectedDestination1],
            [$destination, $source2, $expectedDestination2],
            [$destination, $source3, $expectedDestination3],
            [$destination, $source4, $destination]
        ];
    }

    /**
     * Tests the mergeData method.
     * @param Recipe $destination
     * @param Recipe $source
     * @param Recipe $expectedDestination
     * @throws ReflectionException
     * @covers ::mergeData
     * @dataProvider provideMergeData
     */
    public function testMergeData(Recipe $destination, Recipe $source, Recipe $expectedDestination): void
    {
        /* @var EntityRegistry $recipeRegistry */
        $recipeRegistry = $this->createMock(EntityRegistry::class);

        $merger = new RecipeMerger($recipeRegistry);

        $this->invokeMethod($merger, 'mergeData', $destination, $source);
        $this->assertEquals($expectedDestination, $destination);
    }

    /**
     * Tests the mergeTranslations method.
     * @throws ReflectionException
     * @covers ::mergeTranslations
     */
    public function testMergeTranslations(): void
    {
        $destination = new Recipe();
        $destination->getLabels()->setTranslation('en', 'abc')
                    ->setTranslation('de', 'def');
        $destination->getDescriptions()->setTranslation('en', 'ghi')
                    ->setTranslation('de', 'jkl');

        $source = new Recipe();
        $source->getLabels()->setTranslation('en', 'mno')
               ->setTranslation('fr', 'pqr');
        $source->getDescriptions()->setTranslation('en', 'stu')
               ->setTranslation('fr', 'vwx');

        $expectedDestination = new Recipe();
        $expectedDestination->getLabels()->setTranslation('en', 'mno')
                            ->setTranslation('de', 'def')
                            ->setTranslation('fr', 'pqr');
        $expectedDestination->getDescriptions()->setTranslation('en', 'stu')
                            ->setTranslation('de', 'jkl')
                            ->setTranslation('fr', 'vwx');

        /* @var EntityRegistry $recipeRegistry */
        $recipeRegistry = $this->createMock(EntityRegistry::class);

        $merger = new RecipeMerger($recipeRegistry);

        $this->invokeMethod($merger, 'mergeTranslations', $destination, $source);
        $this->assertEquals($expectedDestination, $destination);
    }

    /**
     * Provides the data for the mergeIcon test.
     * @return array
     */
    public function provideMergeIcon(): array
    {
        return [
            ['abc', true],
            ['', false],
        ];
    }

    /**
     * Tests the mergeIcon method.
     * @param string $sourceIconHash
     * @param bool $expectDestinationIconHash
     * @throws ReflectionException
     * @covers ::mergeIcon
     * @dataProvider provideMergeIcon
     */
    public function testMergeIcon(string $sourceIconHash, bool $expectDestinationIconHash): void
    {
        $source = new Recipe();
        $source->setIconHash($sourceIconHash);

        /* @var Recipe|MockObject $destination */
        $destination = $this->getMockBuilder(Recipe::class)
                            ->setMethods(['setIconHash'])
                            ->disableOriginalConstructor()
                            ->getMock();
        $destination->expects($expectDestinationIconHash ? $this->once() : $this->never())
                    ->method('setIconHash')
                    ->with($sourceIconHash);

        /* @var EntityRegistry $recipeRegistry */
        $recipeRegistry = $this->createMock(EntityRegistry::class);

        $merger = new RecipeMerger($recipeRegistry);

        $this->invokeMethod($merger, 'mergeIcon', $destination, $source);
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

        /* @var EntityRegistry $recipeRegistry */
        $recipeRegistry = $this->createMock(EntityRegistry::class);

        $merger = new RecipeMerger($recipeRegistry);

        $this->invokeMethod($merger, 'setHashesToCombination', $combination, $hashes);
    }
}
