<?php

namespace FactorioItemBrowserTest\Export\Reducer;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Export\Combination\ParentCombinationFinder;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Reducer\ReducerInterface;
use FactorioItemBrowser\Export\Reducer\ReducerManager;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the ReducerManager class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Reducer\ReducerManager
 */
class ReducerManagerTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests the constructing.
     * @covers ::__construct
     * @throws ReflectionException
     */
    public function testConstruct(): void
    {
        /* @var ParentCombinationFinder $parentCombinationFinder */
        $parentCombinationFinder = $this->createMock(ParentCombinationFinder::class);
        /* @var ReducerInterface $reducer1 */
        $reducer1 = $this->createMock(ReducerInterface::class);
        /* @var ReducerInterface $reducer2 */
        $reducer2 = $this->createMock(ReducerInterface::class);
        $reducers = [$reducer1, $reducer2];

        $manager = new ReducerManager($parentCombinationFinder, $reducers);

        $this->assertSame($parentCombinationFinder, $this->extractProperty($manager, 'parentCombinationFinder'));
        $this->assertSame($reducers, $this->extractProperty($manager, 'reducers'));
    }

    /**
     * Tests the reduce method.
     * @covers ::reduce
     * @throws ExportException
     */
    public function testReduce(): void
    {
        $combination = (new Combination())->setName('abc');
        $mergedParentCombination = (new Combination())->setName('def');

        /* @var ParentCombinationFinder|MockObject $parentCombinationFinder */
        $parentCombinationFinder = $this->getMockBuilder(ParentCombinationFinder::class)
                                        ->setMethods(['getMergedParentCombination'])
                                        ->disableOriginalConstructor()
                                        ->getMock();
        $parentCombinationFinder->expects($this->once())
                                ->method('getMergedParentCombination')
                                ->with($combination)
                                ->willReturn($mergedParentCombination);

        /* @var ReducerManager|MockObject $manager */
        $manager = $this->getMockBuilder(ReducerManager::class)
                        ->setMethods(['reduceCombination'])
                        ->setConstructorArgs([$parentCombinationFinder, []])
                        ->getMock();
        $manager->expects($this->once())
                ->method('reduceCombination')
                ->with($combination, $mergedParentCombination);

        $result = $manager->reduce($combination);
        $this->assertNotSame($combination, $result);
    }

    /**
     * Tests the reduceCombination method.
     * @throws ReflectionException
     * @covers ::reduceCombination
     */
    public function testReduceCombination(): void
    {
        $combination = (new Combination())->setName('abc');
        $parentCombination = (new Combination())->setName('def');
        
        /* @var ReducerInterface|MockObject $reducer1 */
        $reducer1 = $this->getMockBuilder(ReducerInterface::class)
                         ->setMethods(['reduce'])
                         ->getMockForAbstractClass();
        $reducer1->expects($this->once())
                 ->method('reduce')
                 ->with($combination, $parentCombination);

        /* @var ReducerInterface|MockObject $reducer2 */
        $reducer2 = $this->getMockBuilder(ReducerInterface::class)
                         ->setMethods(['reduce'])
                         ->getMockForAbstractClass();
        $reducer2->expects($this->once())
                 ->method('reduce')
                 ->with($combination, $parentCombination);

        /* @var ParentCombinationFinder $parentCombinationFinder */
        $parentCombinationFinder = $this->createMock(ParentCombinationFinder::class);

        $manager = new ReducerManager($parentCombinationFinder, [$reducer1, $reducer2]);

        $this->invokeMethod($manager, 'reduceCombination', $combination, $parentCombination);
    }
}
