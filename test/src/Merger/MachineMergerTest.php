<?php

namespace FactorioItemBrowserTest\Export\Merger;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Export\Exception\MergerException;
use FactorioItemBrowser\Export\Merger\MachineMerger;
use FactorioItemBrowser\ExportData\Entity\EntityInterface;
use FactorioItemBrowser\ExportData\Entity\Item;
use FactorioItemBrowser\ExportData\Entity\Machine;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;
use FactorioItemBrowser\ExportData\Registry\EntityRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the MachineMerger class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Merger\MachineMerger
 */
class MachineMergerTest extends TestCase
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
                            ->setMethods(['getMachineHashes'])
                            ->disableOriginalConstructor()
                            ->getMock();
        $combination->expects($this->once())
                    ->method('getMachineHashes')
                    ->willReturn($hashes);

        /* @var EntityRegistry $machineRegistry */
        $machineRegistry = $this->createMock(EntityRegistry::class);

        $merger = new MachineMerger($machineRegistry);

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
            [new Machine(), new Machine(), false],
            [new Machine(), new Item(), true],
            [new Item(), new Machine(), true],
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
        /* @var MachineMerger|MockObject $merger */
        $merger = $this->getMockBuilder(MachineMerger::class)
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
                            ->setMethods(['setMachineHashes'])
                            ->disableOriginalConstructor()
                            ->getMock();
        $combination->expects($this->once())
                    ->method('setMachineHashes')
                    ->with($hashes);

        /* @var EntityRegistry $machineRegistry */
        $machineRegistry = $this->createMock(EntityRegistry::class);

        $merger = new MachineMerger($machineRegistry);

        $this->invokeMethod($merger, 'setHashesToCombination', $combination, $hashes);
    }
}
