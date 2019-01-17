<?php

namespace FactorioItemBrowserTest\Export\Reducer;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Export\Exception\ReducerException;
use FactorioItemBrowser\Export\Reducer\MachineReducer;
use FactorioItemBrowser\ExportData\Entity\EntityInterface;
use FactorioItemBrowser\ExportData\Entity\Item;
use FactorioItemBrowser\ExportData\Entity\Machine;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;
use FactorioItemBrowser\ExportData\Registry\EntityRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the MachineReducer class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Reducer\MachineReducer
 */
class MachineReducerTest extends TestCase
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

        /* @var EntityRegistry $rawMachineRegistry */
        $rawMachineRegistry = $this->createMock(EntityRegistry::class);
        /* @var EntityRegistry $reducedMachineRegistry */
        $reducedMachineRegistry = $this->createMock(EntityRegistry::class);

        $reducer = new MachineReducer($rawMachineRegistry, $reducedMachineRegistry);

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
            [new Machine(), new Machine(), false],
            [new Machine(), new Item(), true],
            [new Item(), new Machine(), true],
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
        /* @var MachineReducer|MockObject $reducer */
        $reducer = $this->getMockBuilder(MachineReducer::class)
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
        $machine = new Machine();
        $machine->setName('foo')
                ->setCraftingCategories(['def', 'abc'])
                ->setCraftingSpeed(4.2)
                ->setNumberOfItemSlots(12)
                ->setNumberOfFluidInputSlots(23)
                ->setNumberOfFluidOutputSlots(34)
                ->setNumberOfModuleSlots(45)
                ->setEnergyUsage(13.37)
                ->setEnergyUsageUnit('ghi');

        $parentMachine = (new Machine())->setName('bar');
        $expectedMachine = $expectReduction ? (new Machine())->setName('foo') : $machine;

        /* @var MachineReducer|MockObject $reducer */
        $reducer = $this->getMockBuilder(MachineReducer::class)
                        ->setMethods(['calculateDataHash'])
                        ->disableOriginalConstructor()
                        ->getMock();
        $reducer->expects($this->exactly(2))
                ->method('calculateDataHash')
                ->withConsecutive(
                    [$machine],
                    [$parentMachine]
                )
                ->willReturnOnConsecutiveCalls(
                    $hash,
                    $parentHash
                );

        $this->invokeMethod($reducer, 'reduceData', $machine, $parentMachine);
        $this->assertEquals($expectedMachine, $machine);
    }

    /**
     * Tests the calculateDataHash method.
     * @throws ReflectionException
     * @covers ::calculateDataHash
     */
    public function testCalculateDataHash(): void
    {
        $machine = new Machine();
        $machine->setCraftingCategories(['def', 'abc'])
                ->setCraftingSpeed(4.2)
                ->setNumberOfItemSlots(12)
                ->setNumberOfFluidInputSlots(23)
                ->setNumberOfFluidOutputSlots(34)
                ->setNumberOfModuleSlots(45)
                ->setEnergyUsage(13.37)
                ->setEnergyUsageUnit('ghi')
                ->setName('foo');

        $expectedResult = '3d8217714a401f0c';

        /* @var EntityRegistry $rawMachineRegistry */
        $rawMachineRegistry = $this->createMock(EntityRegistry::class);
        /* @var EntityRegistry $reducedMachineRegistry */
        $reducedMachineRegistry = $this->createMock(EntityRegistry::class);

        $reducer = new MachineReducer($rawMachineRegistry, $reducedMachineRegistry);

        $result = $this->invokeMethod($reducer, 'calculateDataHash', $machine);
        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the reduceTranslations method.
     * @throws ReflectionException
     * @covers ::reduceTranslations
     */
    public function testReduceTranslations(): void
    {
        $machine = new Machine();
        $machine->getLabels()->setTranslation('en', 'abc')
                ->setTranslation('de', 'def');
        $machine->getDescriptions()->setTranslation('en', 'ghi')
                ->setTranslation('de', 'jkl');

        $parentMachine = new Machine();
        $parentMachine->getLabels()->setTranslation('en', 'abc')
                      ->setTranslation('de', 'mno');
        $parentMachine->getDescriptions()->setTranslation('en', 'ghi')
                      ->setTranslation('de', 'pqr');

        $expectedMachine = new Machine();
        $expectedMachine->getLabels()->setTranslation('de', 'def');
        $expectedMachine->getDescriptions()->setTranslation('de', 'jkl');

        /* @var EntityRegistry $rawMachineRegistry */
        $rawMachineRegistry = $this->createMock(EntityRegistry::class);
        /* @var EntityRegistry $reducedMachineRegistry */
        $reducedMachineRegistry = $this->createMock(EntityRegistry::class);

        $reducer = new MachineReducer($rawMachineRegistry, $reducedMachineRegistry);

        $this->invokeMethod($reducer, 'reduceTranslations', $machine, $parentMachine);
        $this->assertEquals($expectedMachine, $machine);
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
        /* @var Machine|MockObject $machine */
        $machine = $this->getMockBuilder(Machine::class)
                        ->setMethods(['getIconHash', 'setIconHash'])
                        ->disableOriginalConstructor()
                        ->getMock();
        $machine->expects($this->once())
                ->method('getIconHash')
                ->willReturn($iconHash);
        $machine->expects($expectSet ? $this->once() : $this->never())
                ->method('setIconHash')
                ->with('');

        /* @var Machine|MockObject $parentMachine */
        $parentMachine = $this->getMockBuilder(Machine::class)
                              ->setMethods(['getIconHash'])
                              ->disableOriginalConstructor()
                              ->getMock();
        $parentMachine->expects($this->once())
                      ->method('getIconHash')
                      ->willReturn($parentIconHash);

        /* @var EntityRegistry $rawMachineRegistry */
        $rawMachineRegistry = $this->createMock(EntityRegistry::class);
        /* @var EntityRegistry $reducedMachineRegistry */
        $reducedMachineRegistry = $this->createMock(EntityRegistry::class);

        $reducer = new MachineReducer($rawMachineRegistry, $reducedMachineRegistry);

        $this->invokeMethod($reducer, 'reduceIcon', $machine, $parentMachine);
    }
    
    /**
     * Provides the data for the isEntityEmpty test.
     * @return array
     */
    public function provideIsEntityEmpty(): array
    {
        $entity1 = new Machine();

        $entity2 = new Machine();
        $entity2->setCraftingCategories(['abc', 'def']);

        $entity3 = new Machine();
        $entity3->setIconHash('abc');

        $entity4 = new Machine();
        $entity4->getLabels()->setTranslation('en', 'ghi');

        $entity5 = new Machine();
        $entity5->getDescriptions()->setTranslation('de', 'jkl');

        return [
            [$entity1, false, true],
            [$entity2, false, false],
            [$entity3, false, false],
            [$entity4, false, false],
            [$entity5, false, false],
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

        /* @var EntityRegistry $rawMachineRegistry */
        $rawMachineRegistry = $this->createMock(EntityRegistry::class);
        /* @var EntityRegistry $reducedMachineRegistry */
        $reducedMachineRegistry = $this->createMock(EntityRegistry::class);

        $reducer = new MachineReducer($rawMachineRegistry, $reducedMachineRegistry);
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
                            ->setMethods(['setMachineHashes'])
                            ->disableOriginalConstructor()
                            ->getMock();
        $combination->expects($this->once())
                    ->method('setMachineHashes')
                    ->with($hashes);

        /* @var EntityRegistry $rawMachineRegistry */
        $rawMachineRegistry = $this->createMock(EntityRegistry::class);
        /* @var EntityRegistry $reducedMachineRegistry */
        $reducedMachineRegistry = $this->createMock(EntityRegistry::class);

        $reducer = new MachineReducer($rawMachineRegistry, $reducedMachineRegistry);

        $this->invokeMethod($reducer, 'setHashesToCombination', $combination, $hashes);
    }
}
