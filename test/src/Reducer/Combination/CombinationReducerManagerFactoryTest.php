<?php

namespace FactorioItemBrowserTest\Export\Reducer\Combination;

use FactorioItemBrowser\Export\Combination\ParentCombinationFinder;
use FactorioItemBrowser\Export\Reducer\Combination\IconReducer;
use FactorioItemBrowser\Export\Reducer\Combination\ItemReducer;
use FactorioItemBrowser\Export\Reducer\Combination\MachineReducer;
use FactorioItemBrowser\Export\Reducer\Combination\RecipeReducer;
use FactorioItemBrowser\Export\Reducer\Combination\CombinationReducerManager;
use FactorioItemBrowser\Export\Reducer\Combination\CombinationReducerManagerFactory;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the CombinationReducerManagerFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Reducer\Combination\CombinationReducerManagerFactory
 */
class CombinationReducerManagerFactoryTest extends TestCase
{
    /**
     * Tests the invoking.
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        /* @var ContainerInterface|MockObject $container */
        $container = $this->getMockBuilder(ContainerInterface::class)
                          ->setMethods(['get'])
                          ->getMockForAbstractClass();
        $container->expects($this->exactly(5))
                  ->method('get')
                  ->withConsecutive(
                      [ParentCombinationFinder::class],
                      [IconReducer::class],
                      [ItemReducer::class],
                      [MachineReducer::class],
                      [RecipeReducer::class]
                  )
                  ->willReturnOnConsecutiveCalls(
                      $this->createMock(ParentCombinationFinder::class),
                      $this->createMock(IconReducer::class),
                      $this->createMock(ItemReducer::class),
                      $this->createMock(MachineReducer::class),
                      $this->createMock(RecipeReducer::class)
                  );

        $factory = new CombinationReducerManagerFactory();
        $factory($container, CombinationReducerManager::class);
    }
}
