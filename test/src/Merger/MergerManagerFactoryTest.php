<?php

namespace FactorioItemBrowserTest\Export\Merger;

use FactorioItemBrowser\Export\Merger\IconMerger;
use FactorioItemBrowser\Export\Merger\ItemMerger;
use FactorioItemBrowser\Export\Merger\MachineMerger;
use FactorioItemBrowser\Export\Merger\MergerManager;
use FactorioItemBrowser\Export\Merger\MergerManagerFactory;
use FactorioItemBrowser\Export\Merger\RecipeMerger;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the MergerManagerFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Merger\MergerManagerFactory
 */
class MergerManagerFactoryTest extends TestCase
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
        $container->expects($this->exactly(4))
                  ->method('get')
                  ->withConsecutive(
                      [IconMerger::class],
                      [ItemMerger::class],
                      [MachineMerger::class],
                      [RecipeMerger::class]
                  )
                  ->willReturnOnConsecutiveCalls(
                      $this->createMock(IconMerger::class),
                      $this->createMock(ItemMerger::class),
                      $this->createMock(MachineMerger::class),
                      $this->createMock(RecipeMerger::class)
                  );

        $factory = new MergerManagerFactory();
        $factory($container, MergerManager::class);
    }
}
