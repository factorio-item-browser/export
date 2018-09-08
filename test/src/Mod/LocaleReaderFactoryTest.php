<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Mod;

use FactorioItemBrowser\Export\Cache\LocaleCache;
use FactorioItemBrowser\Export\Mod\LocaleReader;
use FactorioItemBrowser\Export\Mod\LocaleReaderFactory;
use FactorioItemBrowser\Export\Mod\ModFileManager;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the LocaleReaderFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Mod\LocaleReaderFactory
 */
class LocaleReaderFactoryTest extends TestCase
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
                      [ModFileManager::class]
                  )
                  ->willReturnOnConsecutiveCalls(
                      $this->createMock(LocaleCache::class),
                      $this->createMock(ModFileManager::class)
                  );

        $factory = new LocaleReaderFactory();
        $factory($container, LocaleReader::class);
    }
}
