<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Mapper;

use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Export\Entity\Dump\Icon as DumpIcon;
use FactorioItemBrowser\Export\Entity\Dump\Layer as DumpLayer;
use FactorioItemBrowser\Export\Mapper\IconMapper;
use FactorioItemBrowser\ExportData\Entity\Icon as ExportIcon;
use FactorioItemBrowser\ExportData\Entity\Icon\Layer as ExportLayer;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the IconMapper class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Mapper\IconMapper
 */
class IconMapperTest extends TestCase
{
    /**
     * @covers ::getSupportedDestinationClass
     * @covers ::getSupportedSourceClass
     */
    public function testGetSupportedClasses(): void
    {
        $instance = new IconMapper();

        $this->assertSame(DumpIcon::class, $instance->getSupportedSourceClass());
        $this->assertSame(ExportIcon::class, $instance->getSupportedDestinationClass());
    }

    /**
     * @covers ::map
     */
    public function testMap(): void
    {
        $sourceLayer1 = $this->createMock(DumpLayer::class);
        $sourceLayer2 = $this->createMock(DumpLayer::class);

        $destinationLayer1 = $this->createMock(ExportLayer::class);
        $destinationLayer2 = $this->createMock(ExportLayer::class);

        $source = new DumpIcon();
        $source->layers = [$sourceLayer1, $sourceLayer2];

        $expectedDestination = new ExportIcon();
        $expectedDestination->size = 64;
        $expectedDestination->layers = [$destinationLayer1, $destinationLayer2];

        $destination = new ExportIcon();

        $mapperManager = $this->createMock(MapperManagerInterface::class);
        $mapperManager->expects($this->exactly(2))
                      ->method('map')
                      ->withConsecutive(
                          [$this->identicalTo($sourceLayer1), $this->isInstanceOf(ExportLayer::class)],
                          [$this->identicalTo($sourceLayer2), $this->isInstanceOf(ExportLayer::class)],
                      )
                      ->willReturnOnConsecutiveCalls(
                          $destinationLayer1,
                          $destinationLayer2
                      );

        $instance = new IconMapper();
        $instance->setMapperManager($mapperManager);
        $instance->map($source, $destination);

        $this->assertEquals($expectedDestination, $destination);
    }
}
