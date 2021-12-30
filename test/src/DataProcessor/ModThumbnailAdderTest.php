<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\DataProcessor;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Export\DataProcessor\ModThumbnailAdder;
use FactorioItemBrowser\Export\Exception\FileNotFoundInModException;
use FactorioItemBrowser\Export\Output\Console;
use FactorioItemBrowser\Export\Service\ModFileService;
use FactorioItemBrowser\ExportData\Collection\ChunkedCollection;
use FactorioItemBrowser\ExportData\Entity\Icon;
use FactorioItemBrowser\ExportData\Entity\Icon\Layer;
use FactorioItemBrowser\ExportData\Entity\Mod;
use FactorioItemBrowser\ExportData\ExportData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the ModThumbnailAdder class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Export\DataProcessor\ModThumbnailAdder
 */
class ModThumbnailAdderTest extends TestCase
{
    use ReflectionTrait;

    /** @var Console&MockObject */
    private Console $console;
    /** @var ModFileService&MockObject */
    private ModFileService $modFileService;

    protected function setUp(): void
    {
        $this->console = $this->createMock(Console::class);
        $this->modFileService = $this->createMock(ModFileService::class);
    }

    /**
     * @param array<string> $mockedMethods
     * @return ModThumbnailAdder&MockObject
     */
    private function createInstance(array $mockedMethods = []): ModThumbnailAdder
    {
        return $this->getMockBuilder(ModThumbnailAdder::class)
                    ->onlyMethods($mockedMethods)
                    ->setConstructorArgs([
                        $this->console,
                        $this->modFileService,
                    ])
                    ->getMock();
    }

    public function testProcess(): void
    {
        $mod1 = $this->createMock(Mod::class);
        $mod2 = $this->createMock(Mod::class);
        $mod3 = $this->createMock(Mod::class);

        $thumbnail1 = $this->createMock(Icon::class);
        $thumbnail3 = $this->createMock(Icon::class);
        $mods = $this->createMock(ChunkedCollection::class);

        $icons = $this->createMock(ChunkedCollection::class);
        $icons->expects($this->exactly(2))
              ->method('add')
              ->withConsecutive(
                  [$this->identicalTo($thumbnail1)],
                  [$this->identicalTo($thumbnail3)],
              );

        $exportData = $this->createMock(ExportData::class);
        $exportData->expects($this->any())
                   ->method('getIcons')
                   ->willReturn($icons);
        $exportData->expects($this->any())
                   ->method('getMods')
                   ->willReturn($mods);

        $this->console->expects($this->once())
                      ->method('iterateWithProgressbar')
                      ->with($this->isType('string'), $this->identicalTo($mods))
                      ->willReturnCallback(fn() => yield from [$mod1, $mod2, $mod3]);

        $instance = $this->createInstance(['createThumbnail']);
        $instance->expects($this->exactly(3))
                 ->method('createThumbnail')
                 ->withConsecutive(
                     [$this->identicalTo($mod1)],
                     [$this->identicalTo($mod2)],
                     [$this->identicalTo($mod3)],
                 )
                 ->willReturnOnConsecutiveCalls(
                     $thumbnail1,
                     null,
                     $thumbnail3,
                 );

        $instance->process($exportData);
    }

    /**
     * @throws ReflectionException
     */
    public function testCreateThumbnail(): void
    {
        $modName = 'abc';
        $size = 32;

        $mod = new Mod();
        $mod->name = $modName;

        $expectedLayer = new Layer();
        $expectedLayer->fileName = '__abc__/thumbnail.png';
        $expectedLayer->size = $size;

        $expectedResult = new Icon();
        $expectedResult->type = 'mod';
        $expectedResult->name = $modName;
        $expectedResult->size = 144;
        $expectedResult->layers = [$expectedLayer];

        $instance = $this->createInstance(['getThumbnailSize']);
        $instance->expects($this->once())
                 ->method('getThumbnailSize')
                 ->with($this->identicalTo($mod))
                 ->willReturn($size);

        $result = $this->invokeMethod($instance, 'createThumbnail', $mod);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @throws ReflectionException
     */
    public function testCreateThumbnailWithoutSize(): void
    {
        $modName = 'abc';

        $mod = new Mod();
        $mod->name = $modName;

        $instance = $this->createInstance(['getThumbnailSize']);
        $instance->expects($this->once())
                 ->method('getThumbnailSize')
                 ->with($this->identicalTo($mod))
                 ->willReturn(0);

        $result = $this->invokeMethod($instance, 'createThumbnail', $mod);

        $this->assertNull($result);
    }

    /**
     * @throws ReflectionException
     */
    public function testGetThumbnailSize(): void
    {
        $modName = 'abc';
        $content = file_get_contents(__DIR__ . '/../../asset/icon.png');
        $expectedResult = 32;

        $mod = new Mod();
        $mod->name = $modName;

        $this->modFileService->expects($this->once())
                             ->method('readFile')
                             ->with($this->identicalTo($modName), $this->identicalTo('thumbnail.png'))
                             ->willReturn($content);

        $instance = $this->createInstance();
        $result = $this->invokeMethod($instance, 'getThumbnailSize', $mod);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * @throws ReflectionException
     */
    public function testGetThumbnailSizeWithoutThumbnail(): void
    {
        $modName = 'abc';
        $expectedResult = 0;

        $mod = new Mod();
        $mod->name = $modName;

        $this->modFileService->expects($this->once())
                             ->method('readFile')
                             ->with($this->identicalTo($modName), $this->identicalTo('thumbnail.png'))
                             ->willThrowException($this->createMock(FileNotFoundInModException::class));

        $instance = $this->createInstance();
        $result = $this->invokeMethod($instance, 'getThumbnailSize', $mod);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * @throws ReflectionException
     */
    public function testGetThumbnailSizeWithInvalidContent(): void
    {
        $modName = 'abc';
        $content = 'not-an-image';
        $expectedResult = 0;

        $mod = new Mod();
        $mod->name = $modName;

        $this->modFileService->expects($this->once())
                             ->method('readFile')
                             ->with($this->identicalTo($modName), $this->identicalTo('thumbnail.png'))
                             ->willReturn($content);

        $instance = $this->createInstance();
        $result = $this->invokeMethod($instance, 'getThumbnailSize', $mod);

        $this->assertSame($expectedResult, $result);
    }
}
