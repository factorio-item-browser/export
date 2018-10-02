<?php

namespace FactorioItemBrowserTest\Export\Combination;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Export\Combination\CombinationCreator;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Mod\DependencyResolver;
use FactorioItemBrowser\ExportData\Entity\Mod;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;
use FactorioItemBrowser\ExportData\Registry\EntityRegistry;
use FactorioItemBrowser\ExportData\Registry\ModRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the CombinationCreator class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Combination\CombinationCreator
 */
class CombinationCreatorTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        /* @var EntityRegistry $combinationRegistry */
        $combinationRegistry = $this->createMock(EntityRegistry::class);
        /* @var DependencyResolver $dependencyResolver */
        $dependencyResolver = $this->createMock(DependencyResolver::class);
        /* @var ModRegistry $modRegistry */
        $modRegistry = $this->createMock(ModRegistry::class);

        $creator = new CombinationCreator($combinationRegistry, $dependencyResolver, $modRegistry);

        $this->assertSame($combinationRegistry, $this->extractProperty($creator, 'combinationRegistry'));
        $this->assertSame($dependencyResolver, $this->extractProperty($creator, 'dependencyResolver'));
        $this->assertSame($modRegistry, $this->extractProperty($creator, 'modRegistry'));
    }

    /**
     * Tests the setupForMod method.
     * @throws ReflectionException
     * @covers ::setupForMod
     */
    public function testSetupForMod(): void
    {
        $modName = 'abc';
        $mod = (new Mod())->setName($modName);
        $mandatoryModNames = ['def', 'ghi'];
        $optionalModNames = ['jkl', 'mno'];
        $modOrders = ['jkl' => 42, 'mno' => 1337];

        /* @var DependencyResolver|MockObject $dependencyResolver */
        $dependencyResolver = $this->getMockBuilder(DependencyResolver::class)
                                   ->setMethods(['resolveMandatoryDependencies', 'resolveOptionalDependencies'])
                                   ->disableOriginalConstructor()
                                   ->getMock();
        $dependencyResolver->expects($this->once())
                           ->method('resolveMandatoryDependencies')
                           ->with([$modName])
                           ->willReturn($mandatoryModNames);
        $dependencyResolver->expects($this->once())
                           ->method('resolveOptionalDependencies')
                           ->with([$modName], $mandatoryModNames)
                           ->willReturn($optionalModNames);

        /* @var EntityRegistry $combinationRegistry */
        $combinationRegistry = $this->createMock(EntityRegistry::class);
        /* @var ModRegistry $modRegistry */
        $modRegistry = $this->createMock(ModRegistry::class);

        /* @var CombinationCreator|MockObject $creator */
        $creator = $this->getMockBuilder(CombinationCreator::class)
                        ->setMethods(['getOrdersOfModNames'])
                        ->setConstructorArgs([$combinationRegistry, $dependencyResolver, $modRegistry])
                        ->getMock();
        $creator->expects($this->once())
                ->method('getOrdersOfModNames')
                ->with($optionalModNames)
                ->willReturn($modOrders);

        $creator->setupForMod($mod);

        $this->assertSame($mod, $this->extractProperty($creator, 'mod'));
        $this->assertSame($mandatoryModNames, $this->extractProperty($creator, 'mandatoryModNames'));
        $this->assertSame($optionalModNames, $this->extractProperty($creator, 'optionalModNames'));
        $this->assertSame($modOrders, $this->extractProperty($creator, 'modOrders'));
    }

    /**
     * Tests the getOrdersOfModNames method.
     * @throws ReflectionException
     * @covers ::getOrdersOfModNames
     */
    public function testGetOrdersOfModNames(): void
    {
        $modNames = ['abc', 'def', 'ghi'];
        $mod1 = (new Mod())->setOrder(42);
        $mod2 = (new Mod())->setOrder(1337);
        $expectedResult = ['abc' => 42, 'ghi' => 1337];

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
        /* @var DependencyResolver $dependencyResolver */
        $dependencyResolver = $this->createMock(DependencyResolver::class);

        $creator = new CombinationCreator($combinationRegistry, $dependencyResolver, $modRegistry);

        $result = $this->invokeMethod($creator, 'getOrdersOfModNames', $modNames);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the getNumberOfOptionalMods method.
     * @throws ExportException
     * @throws ReflectionException
     * @covers ::getNumberOfOptionalMods
     */
    public function testGetNumberOfOptionalMods(): void
    {
        $optionalModNames = ['abc', 'def', 'ghi'];
        $expectedResult = 3;

        /* @var CombinationCreator|MockObject $creator */
        $creator = $this->getMockBuilder(CombinationCreator::class)
                        ->setMethods(['verifyMod'])
                        ->disableOriginalConstructor()
                        ->getMock();
        $creator->expects($this->once())
                ->method('verifyMod');
        $this->injectProperty($creator, 'optionalModNames', $optionalModNames);

        $result = $creator->getNumberOfOptionalMods();
        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the createCombinationsWithNumberOfOptionalMods method.
     * @throws ExportException
     * @covers ::createCombinationsWithNumberOfOptionalMods
     */
    public function testCreateCombinationsWithNumberOfOptionalMods(): void
    {
        $numberOfOptionalMods = 42;
        $combination1 = (new Combination())->setName('abc');
        $combination2 = (new Combination())->setName('def');
        $childCombination1 = (new Combination())->setName('ghi');
        $childCombination2 = (new Combination())->setName('jkl');
        $childCombination3 = (new Combination())->setName('mno');
        $expectedResult = [
            'ghi' => $childCombination1,
            'jkl' => $childCombination2,
            'mno' => $childCombination3,
        ];

        /* @var CombinationCreator|MockObject $creator */
        $creator = $this->getMockBuilder(CombinationCreator::class)
                        ->setMethods([
                            'verifyMod',
                            'getCombinationsWithNumberOfOptionalMods',
                            'createChildCombinations'
                        ])
                        ->disableOriginalConstructor()
                        ->getMock();
        $creator->expects($this->once())
                ->method('verifyMod');
        $creator->expects($this->once())
                ->method('getCombinationsWithNumberOfOptionalMods')
                ->with(41)
                ->willReturn([$combination1, $combination2]);
        $creator->expects($this->exactly(2))
                ->method('createChildCombinations')
                ->withConsecutive(
                    [$combination1],
                    [$combination2]
                )
                ->willReturnOnConsecutiveCalls(
                    ['ghi' => $childCombination1, 'jkl' => $childCombination2],
                    ['mno' => $childCombination3]
                );

        $result = $creator->createCombinationsWithNumberOfOptionalMods($numberOfOptionalMods);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Provides the data for the verifyMod test.
     * @return array
     */
    public function provideVerifyMod(): array
    {
        return [
            [(new Mod())->setName('abc'), false],
            [null, true],
        ];
    }

    /**
     * Tests the verifyMod method.
     * @param Mod|null $mod
     * @param bool $expectException
     * @throws ReflectionException
     * @covers ::verifyMod
     * @dataProvider provideVerifyMod
     */
    public function testVerifyMod(?Mod $mod, bool $expectException): void
    {
        /* @var EntityRegistry $combinationRegistry */
        $combinationRegistry = $this->createMock(EntityRegistry::class);
        /* @var DependencyResolver $dependencyResolver */
        $dependencyResolver = $this->createMock(DependencyResolver::class);
        /* @var ModRegistry $modRegistry */
        $modRegistry = $this->createMock(ModRegistry::class);

        if ($expectException) {
            $this->expectException(ExportException::class);
        }

        $creator = new CombinationCreator($combinationRegistry, $dependencyResolver, $modRegistry);
        $this->injectProperty($creator, 'mod', $mod);

        $result = $this->invokeMethod($creator, 'verifyMod');
        $this->assertTrue($result);
    }

    /**
     * Tests the getCombinationsWithNumberOfOptionalMods method.
     * @throws ReflectionException
     * @covers ::getCombinationsWithNumberOfOptionalMods
     */
    public function testGetCombinationsWithNumberOfOptionalMods(): void
    {
        $numberOfOptionalMods = 2;
        $combination1 = new Combination();
        $combination1->setName('abc')
                     ->setLoadedOptionalModNames(['foo', 'bar']);
        $combination2 = new Combination();
        $combination2->setName('def')
                     ->setLoadedOptionalModNames(['foo']);
        $combination3 = new Combination();
        $combination3->setName('jkl')
                     ->setLoadedOptionalModNames(['foo', 'bar']);
        $mod = (new Mod())->setCombinationHashes(['abc', 'def', 'ghi', 'jkl']);
        $expectedResult = [
            'abc' => $combination1,
            'jkl' => $combination3,
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
                $combination2,
                null,
                $combination3
            );

        /* @var DependencyResolver $dependencyResolver */
        $dependencyResolver = $this->createMock(DependencyResolver::class);
        /* @var ModRegistry $modRegistry */
        $modRegistry = $this->createMock(ModRegistry::class);

        $creator = new CombinationCreator($combinationRegistry, $dependencyResolver, $modRegistry);
        $this->injectProperty($creator, 'mod', $mod);

        $result = $this->invokeMethod($creator, 'getCombinationsWithNumberOfOptionalMods', $numberOfOptionalMods);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the createChildCombinations method.
     * @covers ::createChildCombinations
     * @throws ReflectionException
     */
    public function testCreateChildCombinations(): void
    {
        $loadedOptionalModNames = ['foo', 'bar'];
        $optionalModNames = ['abc', 'def', 'ghi', 'jkl'];
        $modOrders = ['abc' => 1337, 'def' => 21, 'jkl' => 7331];
        $highestOrder = 42;

        $combination = (new Combination())->setLoadedOptionalModNames($loadedOptionalModNames);
        $newCombination1 = (new Combination())->setName('mno');
        $newCombination2 = (new Combination())->setName('pqr');

        $expectedResult = ['mno' => $newCombination1, 'pqr' => $newCombination2];

        /* @var CombinationCreator|MockObject $creator */
        $creator = $this->getMockBuilder(CombinationCreator::class)
                        ->setMethods(['getHighestOrderOfMods', 'createCombination'])
                        ->disableOriginalConstructor()
                        ->getMock();
        $creator->expects($this->once())
                ->method('getHighestOrderOfMods')
                ->with($loadedOptionalModNames)
                ->willReturn($highestOrder);
        $creator->expects($this->exactly(2))
                ->method('createCombination')
                ->withConsecutive(
                    [['foo', 'bar', 'abc']],
                    [['foo', 'bar', 'jkl']]
                )
                ->willReturnOnConsecutiveCalls(
                    $newCombination1,
                    $newCombination2
                );
        $this->injectProperty($creator, 'modOrders', $modOrders);
        $this->injectProperty($creator, 'optionalModNames', $optionalModNames);

        $result = $this->invokeMethod($creator, 'createChildCombinations', $combination);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the getHighestOrderOfMods method.
     * @throws ReflectionException
     * @covers ::getHighestOrderOfMods
     */
    public function testGetHighestOrderOfMods(): void
    {
        $modOrders = [
            'abc' => 42,
            'def' => 1337,
            'ghi' => 21,
            'jkl' => 7331,
        ];
        $modNames = ['abc', 'def', 'none', 'ghi'];
        $expectedResult = 1337;

        /* @var EntityRegistry $combinationRegistry */
        $combinationRegistry = $this->createMock(EntityRegistry::class);
        /* @var DependencyResolver $dependencyResolver */
        $dependencyResolver = $this->createMock(DependencyResolver::class);
        /* @var ModRegistry $modRegistry */
        $modRegistry = $this->createMock(ModRegistry::class);

        $creator = new CombinationCreator($combinationRegistry, $dependencyResolver, $modRegistry);
        $this->injectProperty($creator, 'modOrders', $modOrders);

        $result = $this->invokeMethod($creator, 'getHighestOrderOfMods', $modNames);
        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the createCombination method.
     * @throws ReflectionException
     * @covers ::createCombination
     */
    public function testCreateCombination(): void
    {
        $mandatoryModNames = ['abc', 'def'];
        $optionalModNames = ['ghi', 'jkl'];
        $mod = (new Mod())->setName('mno');

        $expectedResult = new Combination();
        $expectedResult->setName('mno-ghi-jkl')
                       ->setMainModName('mno')
                       ->setLoadedModNames(['abc', 'def', 'ghi', 'jkl'])
                       ->setLoadedOptionalModNames(['ghi', 'jkl']);

        /* @var EntityRegistry $combinationRegistry */
        $combinationRegistry = $this->createMock(EntityRegistry::class);
        /* @var DependencyResolver $dependencyResolver */
        $dependencyResolver = $this->createMock(DependencyResolver::class);
        /* @var ModRegistry $modRegistry */
        $modRegistry = $this->createMock(ModRegistry::class);

        $creator = new CombinationCreator($combinationRegistry, $dependencyResolver, $modRegistry);
        $this->injectProperty($creator, 'mod', $mod);
        $this->injectProperty($creator, 'mandatoryModNames', $mandatoryModNames);

        $result = $this->invokeMethod($creator, 'createCombination', $optionalModNames);
        $this->assertEquals($expectedResult, $result);
    }
}
