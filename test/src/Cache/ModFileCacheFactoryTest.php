<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Cache;

use FactorioItemBrowser\Export\Cache\ModFileCache;
use FactorioItemBrowser\Export\Cache\ModFileCacheFactory;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the ModFileCacheFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Cache\ModFileCacheFactory
 */
class ModFileCacheFactoryTest extends TestCase
{
    /**
     * Tests the invoking.
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        $config = [
            'cache' => [
                'mod-file' => [
                    'directory' => 'abc'
                ]
            ]
        ];

        /* @var ContainerInterface|MockObject $container */
        $container = $this->getMockBuilder(ContainerInterface::class)
                          ->setMethods(['get'])
                          ->getMockForAbstractClass();
        $container->expects($this->once())
                  ->method('get')
                  ->with('config')
                  ->willReturn($config);

        $factory = new ModFileCacheFactory();
        $factory($container, ModFileCache::class);
    }
}
