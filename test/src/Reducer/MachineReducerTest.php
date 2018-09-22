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
                        ->setMethods(['reduceDataOfMachine', 'reduceTranslationsOfMachine', 'reduceIconOfMachine'])
                        ->disableOriginalConstructor()
                        ->getMock();
        $reducer->expects($expectException ? $this->never() : $this->once())
                ->method('reduceDataOfMachine')
                ->with($entity, $parentEntity);
        $reducer->expects($expectException ? $this->never() : $this->once())
                ->method('reduceTranslationsOfMachine')
                ->with($entity, $parentEntity);
        $reducer->expects($expectException ? $this->never() : $this->once())
                ->method('reduceIconOfMachine')
                ->with($entity, $parentEntity);

        if ($expectException) {
            $this->expectException(ReducerException::class);
        }

        $this->invokeMethod($reducer, 'reduceEntity', $entity, $parentEntity);
    }

    /**
     * Provides the data for the reduceIconOfMachine test.
     * @return array
     */
    public function provideReduceIconOfMachine(): array
    {
        return [
            ['abc', 'abc', true],
            ['abc', 'def', false],
        ];
    }

    /**
     * Tests the reduceIconOfMachine method.
     * @param string $iconHash
     * @param string $parentIconHash
     * @param bool $expectSet
     * @throws ReflectionException
     * @covers ::reduceIconOfMachine
     * @dataProvider provideReduceIconOfMachine
     */
    public function testReduceIconOfMachine(string $iconHash, string $parentIconHash, bool $expectSet): void
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

        $this->invokeMethod($reducer, 'reduceIconOfMachine', $machine, $parentMachine);
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
