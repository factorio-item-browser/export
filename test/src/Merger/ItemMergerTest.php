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
     * Provides the data for the mergeEntities test.
     * @return array
     */
    public function provideMergeEntities(): array
    {
        return [
            [new Item(), new Item(), false],
            [new Item(), new Recipe(), true],
            [new Recipe(), new Item(), true],
        ];
    }

    /**
     * Tests the mergeEntities method.
     * @param EntityInterface $destination
     * @param EntityInterface $source
     * @param bool $expectException
     * @throws ReflectionException
     * @covers ::mergeEntities
     * @dataProvider provideMergeEntities
     */
    public function testMergeEntities(
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

        $this->invokeMethod($merger, 'mergeEntities', $destination, $source);
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
