<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Command\Clean;

use FactorioItemBrowser\Export\Cache\LocaleCache;
use FactorioItemBrowser\Export\Cache\ModFileCache;
use FactorioItemBrowser\Export\Command\Clean\CleanCacheCommand;
use FactorioItemBrowser\Export\Command\Clean\CleanCacheCommandFactory;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the CleanCacheCommandFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Command\Clean\CleanCacheCommandFactory
 */
class CleanCacheCommandFactoryTest extends TestCase
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
        $container->expects($this->exactly(2))
                  ->method('get')
                  ->withConsecutive(
                      [LocaleCache::class],
                      [ModFileCache::class]
                  )
                  ->willReturnOnConsecutiveCalls(
                      $this->createMock(LocaleCache::class),
                      $this->createMock(ModFileCache::class)
                  );

        $factory = new CleanCacheCommandFactory();
        $factory($container, CleanCacheCommand::class);
    }
}
