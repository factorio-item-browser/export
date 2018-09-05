<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\ModFile;

use FactorioItemBrowser\Export\ModFile\DependencyReader;
use FactorioItemBrowser\Export\ModFile\DependencyReaderFactory;
use FactorioItemBrowser\Export\ModFile\ModFileManager;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the DependencyReaderFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\ModFile\DependencyReaderFactory
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
