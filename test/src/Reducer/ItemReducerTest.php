<?php

namespace FactorioItemBrowserTest\Export\Reducer;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Export\Exception\ReducerException;
use FactorioItemBrowser\Export\Reducer\ItemReducer;
use FactorioItemBrowser\ExportData\Entity\EntityInterface;
use FactorioItemBrowser\ExportData\Entity\Item;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;
use FactorioItemBrowser\ExportData\Entity\Recipe;
use FactorioItemBrowser\ExportData\Registry\EntityRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the ItemReducer class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Reducer\ItemReducer
 */
class ItemReducerTest extends TestCase
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
                            ->setMethods(['getItemHashes'])
                            ->disableOriginalConstructor()
                            ->getMock();
        $combination->expects($this->once())
                    ->method('getItemHashes')
                    ->willReturn($hashes);

        /* @var EntityRegistry $rawItemRegistry */
        $rawItemRegistry = $this->createMock(EntityRegistry::class);
        /* @var EntityRegistry $reducedItemRegistry */
        $reducedItemRegistry = $this->createMock(EntityRegistry::class);

        $reducer = new ItemReducer($rawItemRegistry, $reducedItemRegistry);

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
            [new Item(), new Item(), false],
            [new Item(), new Recipe(), true],
            [new Recipe(), new Item(), true],
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
        /* @var ItemReducer|MockObject $reducer */
        $reducer = $this->getMockBuilder(ItemReducer::class)
                        ->setMethods(['reduceTranslations', 'reduceIcon'])
                        ->disableOriginalConstructor()
                        ->getMock();
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
     * Provides the data for the reduceTranslations test.
     * @return array
     */
    public function provideReduceTranslations(): array
    {
        $item1 = new Item();
        $item1->setProvidesMachineLocalisation(true)
              ->setProvidesRecipeLocalisation(true);   
        $item1->getLabels()->setTranslation('en', 'abc')
                           ->setTranslation('de', 'def');
        $item1->getDescriptions()->setTranslation('en', 'ghi')
                                 ->setTranslation('de', 'jkl');
        
        $parentItem1 = new Item();
        $parentItem1->getLabels()->setTranslation('en', 'abc')
                                 ->setTranslation('de', 'mno');
        $parentItem1->getDescriptions()->setTranslation('en', 'ghi')
                                       ->setTranslation('de', 'pqr');

        $expectedItem1 = new Item();
        $expectedItem1->setProvidesMachineLocalisation(true)
                      ->setProvidesRecipeLocalisation(true);   
        $expectedItem1->getLabels()->setTranslation('de', 'def');
        $expectedItem1->getDescriptions()->setTranslation('de', 'jkl');
        
        $item2 = new Item();
        $item2->setProvidesMachineLocalisation(true)
              ->setProvidesRecipeLocalisation(true);   
        $item2->getLabels()->setTranslation('en', 'abc');
        $item2->getDescriptions()->setTranslation('en', 'ghi');
        
        $parentItem2 = new Item();
        $parentItem2->getLabels()->setTranslation('en', 'abc');
        $parentItem2->getDescriptions()->setTranslation('en', 'ghi');

        $expectedItem2 = new Item();
        $expectedItem2->setProvidesMachineLocalisation(false)
                      ->setProvidesRecipeLocalisation(false);   
        
        return [
            [$item1, $parentItem1, $expectedItem1],
            [$item2, $parentItem2, $expectedItem2],
        ];
    }

    /**
     * Tests the reduceTranslations method.
     * @param Item $item
     * @param Item $parentItem
     * @param Item $expectedItem
     * @throws ReflectionException
     * @covers ::reduceTranslations
     * @dataProvider provideReduceTranslations
     */
    public function testReduceTranslations(Item $item, Item $parentItem, Item $expectedItem): void
    {
        /* @var EntityRegistry $rawItemRegistry */
        $rawItemRegistry = $this->createMock(EntityRegistry::class);
        /* @var EntityRegistry $reducedItemRegistry */
        $reducedItemRegistry = $this->createMock(EntityRegistry::class);

        $reducer = new ItemReducer($rawItemRegistry, $reducedItemRegistry);

        $this->invokeMethod($reducer, 'reduceTranslations', $item, $parentItem);
        $this->assertEquals($expectedItem, $item);
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
        /* @var Item|MockObject $item */
        $item = $this->getMockBuilder(Item::class)
                     ->setMethods(['getIconHash', 'setIconHash'])
                     ->disableOriginalConstructor()
                     ->getMock();
        $item->expects($this->once())
             ->method('getIconHash')
             ->willReturn($iconHash);
        $item->expects($expectSet ? $this->once() : $this->never())
             ->method('setIconHash')
             ->with('');

        /* @var Item|MockObject $parentItem */
        $parentItem = $this->getMockBuilder(Item::class)
                           ->setMethods(['getIconHash'])
                           ->disableOriginalConstructor()
                           ->getMock();
        $parentItem->expects($this->once())
                   ->method('getIconHash')
                   ->willReturn($parentIconHash);

        /* @var EntityRegistry $rawItemRegistry */
        $rawItemRegistry = $this->createMock(EntityRegistry::class);
        /* @var EntityRegistry $reducedItemRegistry */
        $reducedItemRegistry = $this->createMock(EntityRegistry::class);

        $reducer = new ItemReducer($rawItemRegistry, $reducedItemRegistry);

        $this->invokeMethod($reducer, 'reduceIcon', $item, $parentItem);
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
                            ->setMethods(['setItemHashes'])
                            ->disableOriginalConstructor()
                            ->getMock();
        $combination->expects($this->once())
                    ->method('setItemHashes')
                    ->with($hashes);

        /* @var EntityRegistry $rawItemRegistry */
        $rawItemRegistry = $this->createMock(EntityRegistry::class);
        /* @var EntityRegistry $reducedItemRegistry */
        $reducedItemRegistry = $this->createMock(EntityRegistry::class);

        $reducer = new ItemReducer($rawItemRegistry, $reducedItemRegistry);

        $this->invokeMethod($reducer, 'setHashesToCombination', $combination, $hashes);
    }

    /**
     * Provides the data for the isEntityEmpty test.
     * @return array
     */
    public function provideIsEntityEmpty(): array
    {
        $entity1 = new Item();
        $entity1->setIsNew(false);

        $entity2 = new Item();
        $entity2->setIsNew(true);

        $entity3 = new Item();
        $entity3->setIsNew(false)
                ->setIconHash('abc');

        $entity4 = new Item();
        $entity4->setIsNew(false);
        $entity4->getLabels()->setTranslation('en', 'abc');

        $entity5 = new Item();
        $entity5->setIsNew(false);
        $entity5->getDescriptions()->setTranslation('de', 'def');

        return [
            [$entity1, false, true],
            [$entity2, false, false],
            [$entity3, false, false],
            [$entity4, false, false],
            [$entity5, false, false],
            [new Recipe(), true, false],
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

        /* @var EntityRegistry $rawItemRegistry */
        $rawItemRegistry = $this->createMock(EntityRegistry::class);
        /* @var EntityRegistry $reducedItemRegistry */
        $reducedItemRegistry = $this->createMock(EntityRegistry::class);

        $reducer = new ItemReducer($rawItemRegistry, $reducedItemRegistry);
        $result = $this->invokeMethod($reducer, 'isEntityEmpty', $entity);

        $this->assertSame($expectedResult, $result);
    }
}
