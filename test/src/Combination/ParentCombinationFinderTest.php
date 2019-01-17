<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Combination;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Export\Combination\ParentCombinationFinder;
use FactorioItemBrowser\Export\Exception\MergerException;
use FactorioItemBrowser\Export\Merger\MergerManager;
use FactorioItemBrowser\ExportData\Entity\Mod;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;
use FactorioItemBrowser\ExportData\Registry\EntityRegistry;
use FactorioItemBrowser\ExportData\Registry\ModRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the ParentCombinationFinder class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Combination\ParentCombinationFinder
 */
class ParentCombinationFinderTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests the constructing.
     * @covers ::__construct
     * @throws ReflectionException
     */
    public function testConstruct(): void
    {
        /* @var EntityRegistry $combinationRegistry */
        $combinationRegistry = $this->createMock(EntityRegistry::class);
        /* @var MergerManager $mergerManager */
        $mergerManager = $this->createMock(MergerManager::class);
        /* @var ModRegistry $modRegistry */
        $modRegistry = $this->createMock(ModRegistry::class);

        $finder = new ParentCombinationFinder($combinationRegistry, $mergerManager, $modRegistry);
        $this->assertSame($combinationRegistry, $this->extractProperty($finder, 'combinationRegistry'));
        $this->assertSame($modRegistry, $this->extractProperty($finder, 'modRegistry'));
    }

    /**
     * Tests the find method.
     * @covers ::find
     */
    public function testFind(): void
    {
        $combination = (new Combination())->setName('abc');
        $parentCombinations = [(new Combination())->setName('def')];
        $sortedCombinations = [(new Combination())->setName('ghi')];

        /* @var ParentCombinationFinder|MockObject $finder */
        $finder = $this->getMockBuilder(ParentCombinationFinder::class)
                       ->setMethods(['findParentCombinations', 'sortCombinations'])
                       ->disableOriginalConstructor()
                       ->getMock();
        $finder->expects($this->once())
               ->method('findParentCombinations')
               ->with($combination)
               ->willReturn($parentCombinations);
        $finder->expects($this->once())
               ->method('sortCombinations')
               ->with($parentCombinations)
               ->willReturn($sortedCombinations);

        $result = $finder->find($combination);
        $this->assertSame($sortedCombinations, $result);
    }

    /**
     * Tests the getMergedParentCombination method.
     * @throws MergerException
     * @covers ::getMergedParentCombination
     */
    public function testGetMergedParentCombination(): void
    {
        $combination = (new Combination())->setName('abc');
        $parentCombination1 = (new Combination())->setName('def');
        $parentCombination2 = (new Combination())->setName('ghi');
        $resultCombination = null;

        /* @var MergerManager|MockObject $mergerManager */
        $mergerManager = $this->getMockBuilder(MergerManager::class)
                              ->setMethods(['merge'])
                              ->disableOriginalConstructor()
                              ->getMock();
        $mergerManager->expects($this->exactly(2))
                      ->method('merge')
                      ->withConsecutive(
                          [
                              $this->callback(function ($combination) use (&$resultCombination): bool {
                                $resultCombination = $combination;
                                return $combination instanceof Combination;
                              }),
                              $parentCombination1
                          ],
                          [
                              $this->callback(function ($combination) use (&$resultCombination): bool {
                                  $this->assertSame($resultCombination, $combination);
                                  return true;
                              }),
                              $parentCombination2
                          ]
                      );

        /* @var EntityRegistry $combinationRegistry */
        $combinationRegistry = $this->createMock(EntityRegistry::class);
        /* @var ModRegistry $modRegistry */
        $modRegistry = $this->createMock(ModRegistry::class);

        /* @var ParentCombinationFinder|MockObject $finder */
        $finder = $this->getMockBuilder(ParentCombinationFinder::class)
                       ->setMethods(['find'])
                       ->setConstructorArgs([$combinationRegistry, $mergerManager, $modRegistry])
                       ->getMock();
        $finder->expects($this->once())
               ->method('find')
               ->with($combination)
               ->willReturn([$parentCombination1, $parentCombination2]);

        $result = $finder->getMergedParentCombination($combination);
        $this->assertSame($resultCombination, $result);
    }

    /**
     * Tests the findParentCombinations method.
     * @throws ReflectionException
     * @covers ::findParentCombinations
     */
    public function testFindParentCombinations(): void
    {
        $combination = new Combination();
        $combination->setLoadedModNames(['abc', 'def', 'ghi']);

        $mod1 = (new Mod())->setName('abc');
        $mod2 = (new Mod())->setName('ghi');

        $combination1 = (new Combination())->setName('jkl');
        $combination2 = (new Combination())->setName('mno');
        $combination3 = (new Combination())->setName('pqr');
        $parentCombinations1 = [
            'jkl' => $combination1,
            'mno' => $combination2,
        ];
        $parentCombinations2 = [
            'pqr' => $combination3,
        ];
        $expectedResult = [
            'jkl' => $combination1,
            'mno' => $combination2,
            'pqr' => $combination3,
        ];


        /* @var ModRegistry|MockObject $modRegistry */
        $modRegistry = $this->getMockBuilder(ModRegistry::class)
                            ->setMethods(['get'])
                            ->disableOriginalConstructor()
                            ->getMock();
        $modRegistry->expects($this->exactly(3))
                    ->method('get')
                    ->withConsecutive(
                        ['abc'],
                        ['def'],
                        ['ghi']
                    )
                    ->willReturnOnConsecutiveCalls(
                        $mod1,
                        null,
                        $mod2
                    );

        /* @var EntityRegistry $combinationRegistry */
        $combinationRegistry = $this->createMock(EntityRegistry::class);
        /* @var MergerManager $mergerManager */
        $mergerManager = $this->createMock(MergerManager::class);

        /* @var ParentCombinationFinder|MockObject $finder */
        $finder = $this->getMockBuilder(ParentCombinationFinder::class)
                       ->setMethods(['findParentCombinationsOfMod'])
                       ->setConstructorArgs([$combinationRegistry, $mergerManager, $modRegistry])
                       ->getMock();
        $finder->expects($this->exactly(2))
               ->method('findParentCombinationsOfMod')
               ->withConsecutive(
                   [$combination, $mod1],
                   [$combination, $mod2]
               )
               ->willReturnOnConsecutiveCalls(
                   $parentCombinations1,
                   $parentCombinations2
               );

        $result = $this->invokeMethod($finder, 'findParentCombinations', $combination);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the findParentCombinationsOfMod method.
     * @throws ReflectionException
     * @covers ::findParentCombinationsOfMod
     */
    public function testFindParentCombinationsOfMod(): void
    {
        $combination = (new Combination())->setName('foo');
        $mod = new Mod();
        $mod->setCombinationHashes(['abc', 'def', 'ghi', 'jkl']);

        $combination1 = (new Combination())->setName('mno');
        $combination2 = (new Combination())->setName('pqr');
        $combination3 = (new Combination())->setName('stu');

        $expectedResult = [
            'mno' => $combination1,
            'stu' => $combination3,
        ];

        /* @var EntityRegistry|MockObject $combinationRegistry */
        $combinationRegistry = $this->getMockBuilder(EntityRegistry::class)
                                    ->setMethods(['get'])
                                    ->disableOriginalConstructor()
                                    ->getMock();
        $combinationRegistry->expects($this->exactly(4))
                            ->method('get')
                            ->withConsecutive(
                                ['abc'],
                                ['def'],
                                ['ghi'],
                                ['jkl']
                            )
                            ->willReturnOnConsecutiveCalls(
                                $combination1,
                                null,
                                $combination2,
                                $combination3
                            );

        /* @var MergerManager $mergerManager */
        $mergerManager = $this->createMock(MergerManager::class);
        /* @var ModRegistry $modRegistry */
        $modRegistry = $this->createMock(ModRegistry::class);

        /* @var ParentCombinationFinder|MockObject $finder */
        $finder = $this->getMockBuilder(ParentCombinationFinder::class)
                       ->setMethods(['isValidParentCombination'])
                       ->setConstructorArgs([$combinationRegistry, $mergerManager, $modRegistry])
                       ->getMock();
        $finder->expects($this->exactly(3))
               ->method('isValidParentCombination')
               ->withConsecutive(
                   [$combination, $combination1],
                   [$combination, $combination2],
                   [$combination, $combination3]
               )
               ->willReturnOnConsecutiveCalls(
                   true,
                   false,
                   true
               );

        $result = $this->invokeMethod($finder, 'findParentCombinationsOfMod', $combination, $mod);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Provides the data for the isValidParentCombination test.
     * @return array
     */
    public function provideIsValidParentCombination(): array
    {
        $combination = new Combination();
        $combination->setLoadedModNames(['abc', 'def', 'ghi']);

        $parentCombination1 = new Combination();
        $parentCombination1->setLoadedModNames(['abc', 'def']);

        $parentCombination2 = new Combination();
        $parentCombination2->setLoadedModNames(['abc', 'def', 'jkl']);

        return [
            [$combination, $parentCombination1, true],
            [$combination, $parentCombination2, false],
        ];
    }

    /**
     * Tests the isValidParentCombination method.
     * @param Combination $combination
     * @param Combination $parentCombination
     * @param bool $expectedResult
     * @throws ReflectionException
     * @covers ::isValidParentCombination
     * @dataProvider provideIsValidParentCombination
     */
    public function testIsValidParentCombination(
        Combination $combination,
        Combination $parentCombination,
        bool $expectedResult
    ): void {
        /* @var EntityRegistry $combinationRegistry */
        $combinationRegistry = $this->createMock(EntityRegistry::class);
        /* @var MergerManager $mergerManager */
        $mergerManager = $this->createMock(MergerManager::class);
        /* @var ModRegistry $modRegistry */
        $modRegistry = $this->createMock(ModRegistry::class);

        $finder = new ParentCombinationFinder($combinationRegistry, $mergerManager, $modRegistry);
        $result = $this->invokeMethod($finder, 'isValidParentCombination', $combination, $parentCombination);
        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the sortCombinations method.
     * @throws ReflectionException
     * @covers ::sortCombinations
     */
    public function testSortCombinations(): void
    {
        $combination1 = (new Combination())->setName('abc');
        $combination2 = (new Combination())->setName('def');
        $combinations = ['ghi' => $combination1, 'jkl' => $combination2];
        $modOrders = ['mno' => 42];
        $combinationOrders = ['pqr' => [1337, 7331]];
        $expectedResult = ['jkl' => $combination2, 'ghi' => $combination1];

        /* @var ParentCombinationFinder|MockObject $finder */
        $finder = $this->getMockBuilder(ParentCombinationFinder::class)
                       ->setMethods(['getModOrders', 'getCombinationOrders', 'compareCombinations'])
                       ->disableOriginalConstructor()
                       ->getMock();
        $finder->expects($this->once())
               ->method('getModOrders')
               ->willReturn($modOrders);
        $finder->expects($this->once())
               ->method('getCombinationOrders')
               ->with($combinations, $modOrders)
               ->willReturn($combinationOrders);
        $finder->expects($this->atLeast(1))
               ->method('compareCombinations')
               ->with($combination1, $combination2, $modOrders, $combinationOrders)
               ->willReturn(1);

        $result = $this->invokeMethod($finder, 'sortCombinations', $combinations);
        $this->assertSame($expectedResult, $result);
    }

    /**
     * Provides the data for the compareCombinations test.
     * @return array
     */
    public function provideCompareCombinations(): array
    {
        $modOrders = [
            'foo' => 7331,
            'bar' => 1337,
        ];
        $combinationOrders = [
            'def' => [21, 27],
            'ghi' => [21, 27, 42],
            'jkl' => [21, 27, 57],
        ];

        $combination1 = new Combination();
        $combination1->setName('abc')
                     ->setMainModName('foo');

        $combination2 = new Combination();
        $combination2->setName('def')
                     ->setMainModName('bar');

        $combination3 = new Combination();
        $combination3->setName('ghi')
                     ->setMainModName('bar');

        $combination4 = new Combination();
        $combination4->setName('jkl')
                     ->setMainModName('bar');

        return [
            [$combination1, $combination2, $modOrders, $combinationOrders, 1],
            [$combination2, $combination3, $modOrders, $combinationOrders, -1],
            [$combination3, $combination4, $modOrders, $combinationOrders, -1],
            [$combination4, $combination4, $modOrders, $combinationOrders, 0],
        ];
    }

    /**
     * Tests the compareCombinations method.
     * @param Combination $left
     * @param Combination $right
     * @param array $modOrders
     * @param array $combinationOrders
     * @param int $expectedResult
     * @throws ReflectionException
     * @covers ::compareCombinations
     * @dataProvider provideCompareCombinations
     */
    public function testCompareCombinations(
        Combination $left,
        Combination $right,
        array $modOrders,
        array $combinationOrders,
        int $expectedResult
    ): void {
        /* @var EntityRegistry $combinationRegistry */
        $combinationRegistry = $this->createMock(EntityRegistry::class);
        /* @var MergerManager $mergerManager */
        $mergerManager = $this->createMock(MergerManager::class);
        /* @var ModRegistry $modRegistry */
        $modRegistry = $this->createMock(ModRegistry::class);

        $finder = new ParentCombinationFinder($combinationRegistry, $mergerManager, $modRegistry);

        $result = $this->invokeMethod($finder, 'compareCombinations', $left, $right, $modOrders, $combinationOrders);
        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the getModOrders method.
     * @throws ReflectionException
     * @covers ::getModOrders
     */
    public function testGetModOrders(): void
    {
        $mod1 = new Mod();
        $mod1->setName('abc')
             ->setOrder(42);
        $mod2 = new Mod();
        $mod2->setName('def')
             ->setOrder(1337);
        $expectedResult = [
            'abc' => 42,
            'def' => 1337,
        ];

        /* @var ModRegistry|MockObject $modRegistry */
        $modRegistry = $this->getMockBuilder(ModRegistry::class)
                            ->setMethods(['getAllNames', 'get'])
                            ->disableOriginalConstructor()
                            ->getMock();
        $modRegistry->expects($this->once())
                    ->method('getAllNames')
                    ->willReturn(['abc', 'def']);
        $modRegistry->expects($this->exactly(2))
                    ->method('get')
                    ->withConsecutive(
                        ['abc'],
                        ['def']
                    )
                    ->willReturnOnConsecutiveCalls(
                        $mod1,
                        $mod2
                    );

        /* @var EntityRegistry $combinationRegistry */
        $combinationRegistry = $this->createMock(EntityRegistry::class);
        /* @var MergerManager $mergerManager */
        $mergerManager = $this->createMock(MergerManager::class);

        $finder = new ParentCombinationFinder($combinationRegistry, $mergerManager, $modRegistry);
        $result = $this->invokeMethod($finder, 'getModOrders');
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the getCombinationOrders method.
     * @throws ReflectionException
     * @covers ::getCombinationOrders
     */
    public function testGetCombinationOrders(): void
    {
        $modOrders = [
            'abc' => 42,
            'def' => 1337,
            'ghi' => 21,
        ];
        $combination1 = new Combination();
        $combination1->setName('foo')
                     ->setLoadedModNames(['abc', 'def']);
        $combination2 = new Combination();
        $combination2->setName('bar')
                     ->setLoadedModNames(['abc', 'ghi']);
        $combinations = [$combination1, $combination2];
        $expectedResult = [
            'foo' => [42, 1337],
            'bar' => [21, 42],
        ];

        /* @var EntityRegistry $combinationRegistry */
        $combinationRegistry = $this->createMock(EntityRegistry::class);
        /* @var MergerManager $mergerManager */
        $mergerManager = $this->createMock(MergerManager::class);
        /* @var ModRegistry $modRegistry */
        $modRegistry = $this->createMock(ModRegistry::class);

        $finder = new ParentCombinationFinder($combinationRegistry, $mergerManager, $modRegistry);
        $result = $this->invokeMethod($finder, 'getCombinationOrders', $combinations, $modOrders);
        $this->assertEquals($expectedResult, $result);
    }
}
