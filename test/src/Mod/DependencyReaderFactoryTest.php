<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Mod;

use FactorioItemBrowser\Export\Mod\DependencyReader;
use FactorioItemBrowser\Export\Mod\DependencyReaderFactory;
use FactorioItemBrowser\Export\Mod\ModFileManager;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the DependencyReaderFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Mod\DependencyReaderFactory
 */
class DependencyReaderFactoryTest extends TestCase
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
        $container->expects($this->once())
                  ->method('get')
                  ->with(ModFileManager::class)
                  ->willReturn($this->createMock(ModFileManager::class));

        $factory = new DependencyReaderFactory();
        $factory($container, DependencyReader::class);
    }
}
