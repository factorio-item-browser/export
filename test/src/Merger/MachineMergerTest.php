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
     * Provides the data for the mergeEntity test.
     * @return array
     */
    public function provideMergeEntity(): array
    {
        return [
            [new Machine(), new Machine(), false],
            [new Machine(), new Item(), true],
            [new Item(), new Machine(), true],
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

        $this->invokeMethod($merger, 'mergeEntity', $destination, $source);
    }

    /**
     * Provides the data for the mergeData test.
     * @return array
     */
    public function provideMergeData(): array
    {
        $destination = new Machine();
        $destination->setName('foo')
                    ->setCraftingCategories(['abc', 'def'])
                    ->setCraftingSpeed(4.2)
                    ->setNumberOfItemSlots(12)
                    ->setNumberOfFluidInputSlots(23)
                    ->setNumberOfFluidOutputSlots(34)
                    ->setNumberOfModuleSlots(45)
                    ->setEnergyUsage(13.37)
                    ->setEnergyUsageUnit('ghi');

        $source1 = new Machine();
        $source1->setName('bar')
                ->setCraftingCategories(['jkl', 'mno'])
                ->setCraftingSpeed(2.4)
                ->setNumberOfItemSlots(21)
                ->setNumberOfFluidInputSlots(32)
                ->setNumberOfFluidOutputSlots(43)
                ->setNumberOfModuleSlots(54)
                ->setEnergyUsage(73.31)
                ->setEnergyUsageUnit('pqr');


        $source2 = new Machine();
        $source2->setName('bar')
                ->setCraftingSpeed(2.4)
                ->setNumberOfItemSlots(21)
                ->setNumberOfFluidInputSlots(32)
                ->setNumberOfFluidOutputSlots(43)
                ->setNumberOfModuleSlots(54)
                ->setEnergyUsage(73.31)
                ->setEnergyUsageUnit('pqr');

        $expectedDestination1 = new Machine();
        $expectedDestination1->setName('foo')
                             ->setCraftingCategories(['jkl', 'mno'])
                             ->setCraftingSpeed(2.4)
                             ->setNumberOfItemSlots(21)
                             ->setNumberOfFluidInputSlots(32)
                             ->setNumberOfFluidOutputSlots(43)
                             ->setNumberOfModuleSlots(54)
                             ->setEnergyUsage(73.31)
                             ->setEnergyUsageUnit('pqr');

        return [
            [$destination, $source1, $expectedDestination1],
            [$destination, $source2, $destination],
        ];
    }

    /**
     * Tests the mergeData method.
     * @param Machine $destination
     * @param Machine $source
     * @param Machine $expectedDestination
     * @throws ReflectionException
     * @covers ::mergeData
     * @dataProvider provideMergeData
     */
    public function testMergeData(Machine $destination, Machine $source, Machine $expectedDestination): void
    {
        /* @var EntityRegistry $machineRegistry */
        $machineRegistry = $this->createMock(EntityRegistry::class);

        $merger = new MachineMerger($machineRegistry);

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
        $destination = new Machine();
        $destination->getLabels()->setTranslation('en', 'abc')
                                 ->setTranslation('de', 'def');
        $destination->getDescriptions()->setTranslation('en', 'ghi')
                                       ->setTranslation('de', 'jkl');

        $source = new Machine();
        $source->getLabels()->setTranslation('en', 'mno')
                            ->setTranslation('fr', 'pqr');
        $source->getDescriptions()->setTranslation('en', 'stu')
                                  ->setTranslation('fr', 'vwx');

        $expectedDestination = new Machine();
        $expectedDestination->getLabels()->setTranslation('en', 'mno')
                                         ->setTranslation('de', 'def')
                                         ->setTranslation('fr', 'pqr');
        $expectedDestination->getDescriptions()->setTranslation('en', 'stu')
                                               ->setTranslation('de', 'jkl')
                                               ->setTranslation('fr', 'vwx');

        /* @var EntityRegistry $machineRegistry */
        $machineRegistry = $this->createMock(EntityRegistry::class);

        $merger = new MachineMerger($machineRegistry);

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
        $source = new Machine();
        $source->setIconHash($sourceIconHash);

        /* @var Machine|MockObject $destination */
        $destination = $this->getMockBuilder(Machine::class)
                            ->setMethods(['setIconHash'])
                            ->disableOriginalConstructor()
                            ->getMock();
        $destination->expects($expectDestinationIconHash ? $this->once() : $this->never())
                    ->method('setIconHash')
                    ->with($sourceIconHash);

        /* @var EntityRegistry $machineRegistry */
        $machineRegistry = $this->createMock(EntityRegistry::class);

        $merger = new MachineMerger($machineRegistry);

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
