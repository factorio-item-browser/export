<?php

namespace FactorioItemBrowserTest\Export\Reducer;

use FactorioItemBrowser\Export\Combination\ParentCombinationFinder;
use FactorioItemBrowser\Export\Reducer\IconReducer;
use FactorioItemBrowser\Export\Reducer\ItemReducer;
use FactorioItemBrowser\Export\Reducer\MachineReducer;
use FactorioItemBrowser\Export\Reducer\RecipeReducer;
use FactorioItemBrowser\Export\Reducer\ReducerManager;
use FactorioItemBrowser\Export\Reducer\ReducerManagerFactory;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the ReducerManagerFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Reducer\ReducerManagerFactory
 */
class ReducerManagerFactoryTest extends TestCase
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

        $factory = new ReducerManagerFactory();
        $factory($container, ReducerManager::class);
    }
}
