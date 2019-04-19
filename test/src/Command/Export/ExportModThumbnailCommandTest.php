<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Command\Export;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Export\Command\Export\ExportModThumbnailCommand;
use FactorioItemBrowser\Export\Constant\Config;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Mod\ModFileManager;
use FactorioItemBrowser\ExportData\Entity\Icon;
use FactorioItemBrowser\ExportData\Entity\Icon\Layer;
use FactorioItemBrowser\ExportData\Entity\Mod;
use FactorioItemBrowser\ExportData\Registry\EntityRegistry;
use FactorioItemBrowser\ExportData\Registry\ModRegistry;
use Imagine\Image\BoxInterface;
use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ZF\Console\Route;

/**
 * The PHPUnit test of the ExportModThumbnailCommand class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Command\Export\ExportModThumbnailCommand
 */
class ExportModThumbnailCommandTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked icon registry.
     * @var EntityRegistry&MockObject
     */
    protected $iconRegistry;

    /**
     * The mocked imagine.
     * @var ImagineInterface&MockObject
     */
    protected $imagine;

    /**
     * The mocked mod file manager.
     * @var ModFileManager&MockObject
     */
    protected $modFileManager;

    /**
     * The mocked mod registry.
     * @var ModRegistry&MockObject
     */
    protected $modRegistry;

    /**
     * Sets up the test case.
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->iconRegistry = $this->createMock(EntityRegistry::class);
        $this->imagine = $this->createMock(ImagineInterface::class);
        $this->modFileManager = $this->createMock(ModFileManager::class);
        $this->modRegistry = $this->createMock(ModRegistry::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $command = new ExportModThumbnailCommand(
            $this->iconRegistry,
            $this->imagine,
            $this->modFileManager,
            $this->modRegistry
        );

        $this->assertSame($this->iconRegistry, $this->extractProperty($command, 'iconRegistry'));
        $this->assertSame($this->imagine, $this->extractProperty($command, 'imagine'));
        $this->assertSame($this->modFileManager, $this->extractProperty($command, 'modFileManager'));
        $this->assertSame($this->modRegistry, $this->extractProperty($command, 'modRegistry'));
    }

    /**
     * Tests the processMod method.
     * @throws ReflectionException
     * @covers ::processMod
     */
    public function testProcessMod(): void
    {
        $thumbnailHash = 'abc';

        /* @var ImageInterface&MockObject $thumbnail */
        $thumbnail = $this->createMock(ImageInterface::class);
        /* @var Icon&MockObject $icon */
        $icon = $this->createMock(Icon::class);
        /* @var Route&MockObject $route */
        $route = $this->createMock(Route::class);

        /* @var Mod&MockObject $mod */
        $mod = $this->createMock(Mod::class);
        $mod->expects($this->once())
            ->method('setThumbnailHash')
            ->with($this->identicalTo($thumbnailHash));

        $this->iconRegistry->expects($this->once())
                           ->method('set')
                           ->with($this->identicalTo($icon))
                           ->willReturn($thumbnailHash);

        /* @var ExportModThumbnailCommand&MockObject $command */
        $command = $this->getMockBuilder(ExportModThumbnailCommand::class)
                        ->setMethods(['getThumbnailImage', 'createIconEntityFromThumbnail', 'persistMod'])
                        ->setConstructorArgs([
                            $this->iconRegistry,
                            $this->imagine,
                            $this->modFileManager,
                            $this->modRegistry,
                        ])
                        ->getMock();
        $command->expects($this->once())
                ->method('getThumbnailImage')
                ->with($this->identicalTo($mod))
                ->willReturn($thumbnail);
        $command->expects($this->once())
                ->method('createIconEntityFromThumbnail')
                ->with($this->identicalTo($mod), $this->identicalTo($thumbnail))
                ->willReturn($icon);
        $command->expects($this->once())
                ->method('persistMod')
                ->with($this->identicalTo($mod));

        $this->invokeMethod($command, 'processMod', $route, $mod);
    }

    /**
     * Tests the processMod method without an actual thumbnail.
     * @throws ReflectionException
     * @covers ::processMod
     */
    public function testProcessModWithoutThumbnail(): void
    {
        $thumbnail = null;

        /* @var Route&MockObject $route */
        $route = $this->createMock(Route::class);

        /* @var Mod&MockObject $mod */
        $mod = $this->createMock(Mod::class);
        $mod->expects($this->never())
            ->method('setThumbnailHash');

        $this->iconRegistry->expects($this->never())
                           ->method('set');

        /* @var ExportModThumbnailCommand&MockObject $command */
        $command = $this->getMockBuilder(ExportModThumbnailCommand::class)
                        ->setMethods(['getThumbnailImage', 'createIconEntityFromThumbnail', 'persistMod'])
                        ->setConstructorArgs([
                            $this->iconRegistry,
                            $this->imagine,
                            $this->modFileManager,
                            $this->modRegistry,
                        ])
                        ->getMock();
        $command->expects($this->once())
                ->method('getThumbnailImage')
                ->with($this->identicalTo($mod))
                ->willReturn($thumbnail);
        $command->expects($this->never())
                ->method('createIconEntityFromThumbnail');
        $command->expects($this->never())
                ->method('persistMod');

        $this->invokeMethod($command, 'processMod', $route, $mod);
    }

    /**
     * Tests the getThumbnailImage method.
     * @throws ReflectionException
     * @covers ::getThumbnailImage
     */
    public function testGetThumbnailImage(): void
    {
        $imageContent = 'abc';
        
        /* @var Mod&MockObject $mod */
        $mod = $this->createMock(Mod::class);
        /* @var ImageInterface&MockObject $thumbnail */
        $thumbnail = $this->createMock(ImageInterface::class);
        
        $this->modFileManager->expects($this->once())
                             ->method('getFile')
                             ->with($this->identicalTo($mod), $this->identicalTo(Config::THUMBNAIL_FILENAME))
                             ->willReturn($imageContent);
        
        $this->imagine->expects($this->once())
                      ->method('load')
                      ->with($this->identicalTo($imageContent))
                      ->willReturn($thumbnail);

        $command = new ExportModThumbnailCommand(
            $this->iconRegistry,
            $this->imagine,
            $this->modFileManager,
            $this->modRegistry
        );
        $result = $this->invokeMethod($command, 'getThumbnailImage', $mod);

        $this->assertSame($thumbnail, $result);
    }

    /**
     * Tests the getThumbnailImage method.
     * @throws ReflectionException
     * @covers ::getThumbnailImage
     */
    public function testGetThumbnailImageWithException(): void
    {
        /* @var Mod&MockObject $mod */
        $mod = $this->createMock(Mod::class);

        $this->modFileManager->expects($this->once())
                             ->method('getFile')
                             ->with($this->identicalTo($mod), $this->identicalTo(Config::THUMBNAIL_FILENAME))
                             ->willThrowException(new ExportException());

        $this->imagine->expects($this->never())
                      ->method('load');

        $command = new ExportModThumbnailCommand(
            $this->iconRegistry,
            $this->imagine,
            $this->modFileManager,
            $this->modRegistry
        );
        $result = $this->invokeMethod($command, 'getThumbnailImage', $mod);

        $this->assertNull($result);
    }

    /**
     * Tests the createIconEntityFromThumbnail method.
     * @throws ReflectionException
     * @covers ::createIconEntityFromThumbnail
     */
    public function testCreateIconEntityFromThumbnail(): void
    {
        $modName = 'abc';
        $width = 42;

        $expectedLayer = new Layer();
        $expectedLayer->setFileName('__abc__/thumbnail.png');

        $expectedResult = new Icon();
        $expectedResult->addLayer($expectedLayer)
                       ->setSize($width)
                       ->setRenderedSize(Config::THUMBNAIL_SIZE);

        /* @var Mod&MockObject $mod */
        $mod = $this->createMock(Mod::class);
        $mod->expects($this->once())
            ->method('getName')
            ->willReturn($modName);

        /* @var BoxInterface&MockObject $size */
        $size = $this->createMock(BoxInterface::class);
        $size->expects($this->once())
             ->method('getWidth')
             ->willReturn($width);

        /* @var ImageInterface&MockObject $thumbnail */
        $thumbnail = $this->createMock(ImageInterface::class);
        $thumbnail->expects($this->once())
                  ->method('getSize')
                  ->willReturn($size);

        $command = new ExportModThumbnailCommand(
            $this->iconRegistry,
            $this->imagine,
            $this->modFileManager,
            $this->modRegistry
        );
        $result = $this->invokeMethod($command, 'createIconEntityFromThumbnail', $mod, $thumbnail);

        $this->assertEquals($expectedResult, $result);
    }
}
