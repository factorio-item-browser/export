<?php

namespace FactorioItemBrowserTest\Export\Merger;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Export\Exception\MergerException;
use FactorioItemBrowser\Export\Merger\ItemMerger;
use FactorioItemBrowser\ExportData\Entity\EntityInterface;
use FactorioItemBrowser\ExportData\Entity\Item;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;
use FactorioItemBrowser\ExportData\Entity\Recipe;
use FactorioItemBrowser\ExportData\Registry\EntityRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the ItemMerger class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Merger\ItemMerger
 */
class ItemMergerTest extends TestCase
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

        /* @var EntityRegistry $itemRegistry */
        $itemRegistry = $this->createMock(EntityRegistry::class);

        $merger = new ItemMerger($itemRegistry);

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
            [new Item(), new Item(), false],
            [new Item(), new Recipe(), true],
            [new Recipe(), new Item(), true],
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
        /* @var ItemMerger|MockObject $merger */
        $merger = $this->getMockBuilder(ItemMerger::class)
                       ->setMethods(['mergeTranslations', 'mergeIcon'])
                       ->disableOriginalConstructor()
                       ->getMock();
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
     * Provides the data for the mergeTranslations test.
     * @return array
     */
    public function provideMergeTranslations(): array
    {
        $destination = new Item();
        $destination->setProvidesRecipeLocalisation(true)
                    ->setProvidesMachineLocalisation(false);
        $destination->getLabels()->setTranslation('en', 'abc')
                                 ->setTranslation('de', 'def');
        $destination->getDescriptions()->setTranslation('en', 'ghi')
                                       ->setTranslation('de', 'jkl');

        $source1 = new Item();
        $source1->setProvidesRecipeLocalisation(false)
                ->setProvidesMachineLocalisation(true);
        $source1->getLabels()->setTranslation('en', 'mno')
                             ->setTranslation('fr', 'pqr');
        $source1->getDescriptions()->setTranslation('en', 'stu')
                                  ->setTranslation('fr', 'vwx');

        $source2 = new Item();
        $source2->setProvidesRecipeLocalisation(false)
                ->setProvidesMachineLocalisation(true);

        $expectedDestination1 = new Item();
        $expectedDestination1->setProvidesRecipeLocalisation(false)
                             ->setProvidesMachineLocalisation(true);
        $expectedDestination1->getLabels()->setTranslation('en', 'mno')
                                          ->setTranslation('de', 'def')
                                          ->setTranslation('fr', 'pqr');
        $expectedDestination1->getDescriptions()->setTranslation('en', 'stu')
                                                ->setTranslation('de', 'jkl')
                                                ->setTranslation('fr', 'vwx');

        return [
            [$destination, $source1, $expectedDestination1],
            [$destination, $source2, $destination],
        ];
    }

    /**
     * Tests the mergeTranslations method.
     * @param Item $destination
     * @param Item $source
     * @param Item $expectedDestination
     * @throws ReflectionException
     * @covers ::mergeTranslations
     * @dataProvider provideMergeTranslations
     */
    public function testMergeTranslations(Item $destination, Item $source, Item $expectedDestination): void
    {
        /* @var EntityRegistry $itemRegistry */
        $itemRegistry = $this->createMock(EntityRegistry::class);

        $merger = new ItemMerger($itemRegistry);

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
        $source = new Item();
        $source->setIconHash($sourceIconHash);

        /* @var Item|MockObject $destination */
        $destination = $this->getMockBuilder(Item::class)
                            ->setMethods(['setIconHash'])
                            ->disableOriginalConstructor()
                            ->getMock();
        $destination->expects($expectDestinationIconHash ? $this->once() : $this->never())
                    ->method('setIconHash')
                    ->with($sourceIconHash);

        /* @var EntityRegistry $itemRegistry */
        $itemRegistry = $this->createMock(EntityRegistry::class);

        $merger = new ItemMerger($itemRegistry);

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
                            ->setMethods(['setItemHashes'])
                            ->disableOriginalConstructor()
                            ->getMock();
        $combination->expects($this->once())
                    ->method('setItemHashes')
                    ->with($hashes);

        /* @var EntityRegistry $itemRegistry */
        $itemRegistry = $this->createMock(EntityRegistry::class);

        $merger = new ItemMerger($itemRegistry);

        $this->invokeMethod($merger, 'setHashesToCombination', $combination, $hashes);
    }
}
