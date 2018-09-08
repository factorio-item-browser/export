<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Mod;

use FactorioItemBrowser\Export\Cache\ModFileCache;
use FactorioItemBrowser\Export\Mod\ModFileManager;
use FactorioItemBrowser\Export\Mod\ModFileManagerFactory;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the ModFileManagerFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Mod\ModFileManagerFactory
 */
class ModFileManagerFactoryTest extends TestCase
{
    /**
     * Tests the invoking.
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        $config = [
            'factorio' => [
                'modsDirectory' => 'abc',
            ],
        ];

        /* @var ContainerInterface|MockObject $container */
        $container = $this->getMockBuilder(ContainerInterface::class)
                          ->setMethods(['get'])
                          ->getMockForAbstractClass();
        $container->expects($this->exactly(2))
                  ->method('get')
                  ->withConsecutive(
                      ['config'],
                      [ModFileCache::class]
                  )
                  ->willReturnOnConsecutiveCalls(
                      $config,
                      $this->createMock(ModFileCache::class)
                  );

        $factory = new ModFileManagerFactory();
        $factory($container, ModFileManager::class);
    }
}
