<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Parser;

use BluePsyduck\FactorioModPortalClient\Entity\Version;
use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\Entity\InfoJson;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Exception\FileNotFoundInModException;
use FactorioItemBrowser\Export\Helper\HashCalculator;
use FactorioItemBrowser\Export\Output\Console;
use FactorioItemBrowser\Export\Service\ModFileService;
use FactorioItemBrowser\Export\Parser\ModParser;
use FactorioItemBrowser\Export\Parser\TranslationParser;
use FactorioItemBrowser\ExportData\Collection\ChunkedCollection;
use FactorioItemBrowser\ExportData\Entity\Icon;
use FactorioItemBrowser\ExportData\Entity\Icon\Layer;
use FactorioItemBrowser\ExportData\Entity\Mod;
use FactorioItemBrowser\ExportData\ExportData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the ModParser class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Export\Parser\ModParser
 */
class ModParserTest extends TestCase
{
    use ReflectionTrait;

    /** @var Console&MockObject */
    private Console $console;
    /** @var HashCalculator&MockObject */
    private HashCalculator $hashCalculator;
    /** @var ModFileService&MockObject */
    private ModFileService $modFileManager;
    /** @var TranslationParser&MockObject */
    private TranslationParser $translationParser;

    protected function setUp(): void
    {
        $this->console = $this->createMock(Console::class);
        $this->hashCalculator = $this->createMock(HashCalculator::class);
        $this->modFileManager = $this->createMock(ModFileService::class);
        $this->translationParser = $this->createMock(TranslationParser::class);
    }

    /**
     * @param array<string> $methods
     * @return ModParser&MockObject
     */
    private function createInstance(array $methods = []): ModParser
    {
        return $this->getMockBuilder(ModParser::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($methods)
                    ->setConstructorArgs([
                        $this->console,
                        $this->hashCalculator,
                        $this->modFileManager,
                        $this->translationParser,
                    ])
                    ->getMock();
    }

    /**
     * @throws ExportException
     */
    public function testEmptyMethods(): void
    {
        $dump = $this->createMock(Dump::class);
        $exportData = $this->createMock(ExportData::class);

        $instance = $this->createInstance();
        $instance->prepare($dump);
        $instance->validate($exportData);

        $this->addToAssertionCount(1);
    }

    /**
     * @throws ExportException
     */
    public function testParse(): void
    {
        $modNames = ['abc', 'def'];
        $thumbnailId = 'ghi';

        $mod1 = new Mod();
        $mod1->name = 'abc';
        $mod2 = new Mod();
        $mod2->name = 'def';

        $expectedMod1 = new Mod();
        $expectedMod1->name = 'abc';
        $expectedMod1->thumbnailId = $thumbnailId;
        $expectedMod2 = new Mod();
        $expectedMod2->name = 'def';

        $thumbnail = new Icon();
        $thumbnail->id = $thumbnailId;

        $dump = new Dump();
        $dump->modNames = $modNames;

        $mods = $this->createMock(ChunkedCollection::class);
        $mods->expects($this->exactly(2))
             ->method('add')
             ->withConsecutive(
                 [$this->equalTo($expectedMod1)],
                 [$this->equalTo($expectedMod2)],
             );

        $icons = $this->createMock(ChunkedCollection::class);
        $icons->expects($this->once())
              ->method('add')
              ->with($this->identicalTo($thumbnail));

        $exportData = $this->createMock(ExportData::class);
        $exportData->expects($this->any())
                   ->method('getMods')
                   ->willReturn($mods);
        $exportData->expects($this->any())
                   ->method('getIcons')
                   ->willReturn($icons);

        $this->console->expects($this->once())
                      ->method('iterateWithProgressbar')
                      ->with($this->isType('string'), $this->identicalTo($modNames))
                      ->willReturnCallback(fn () => yield from $modNames);

        $instance = $this->createInstance(['createMod', 'createThumbnail']);
        $instance->expects($this->exactly(2))
                 ->method('createMod')
                 ->withConsecutive(
                     [$this->identicalTo('abc')],
                     [$this->identicalTo('def')]
                 )
                 ->willReturnOnConsecutiveCalls(
                     $mod1,
                     $mod2
                 );
        $instance->expects($this->exactly(2))
                 ->method('createThumbnail')
                 ->withConsecutive(
                     [$this->identicalTo($mod1)],
                     [$this->identicalTo($mod2)]
                 )
                 ->willReturnOnConsecutiveCalls(
                     $thumbnail,
                     null
                 );

        $instance->parse($dump, $exportData);
    }

    /**
     * @throws ReflectionException
     */
    public function testCreateMod(): void
    {
        $modName = 'abc';
        $expectedTitleTranslation = ['mod-name.abc'];
        $expectedDescriptionTranslation = ['mod-description.abc'];

        $infoJson = new InfoJson();
        $infoJson->version = new Version('1.2.3');
        $infoJson->author = 'def';
        $infoJson->title = 'ghi';
        $infoJson->description = 'jkl';

        $expectedMod = new Mod();
        $expectedMod->name = 'abc';
        $expectedMod->version = '1.2.3';
        $expectedMod->author = 'def';
        $expectedMod->titles->set('en', 'ghi');
        $expectedMod->descriptions->set('en', 'jkl');

        $this->modFileManager->expects($this->once())
                             ->method('getInfo')
                             ->with($this->identicalTo($modName))
                             ->willReturn($infoJson);

        $this->translationParser->expects($this->exactly(2))
                                ->method('translate')
                                ->withConsecutive(
                                    [
                                        $this->equalTo($expectedMod->titles),
                                        $this->identicalTo($expectedTitleTranslation),
                                    ],
                                    [
                                        $this->equalTo($expectedMod->descriptions),
                                        $this->identicalTo($expectedDescriptionTranslation),
                                    ],
                                );

        $instance = $this->createInstance();
        $result = $this->invokeMethod($instance, 'createMod', $modName);

        $this->assertEquals($expectedMod, $result);
    }

    /**
     * @throws ReflectionException
     */
    public function testCreateThumbnail(): void
    {
        $modName = 'abc';
        $thumbnailId = 'def';
        $size = 32;

        $mod = new Mod();
        $mod->name = $modName;

        $expectedLayer = new Layer();
        $expectedLayer->fileName = '__abc__/thumbnail.png';
        $expectedLayer->size = $size;

        $expectedThumbnail = new Icon();
        $expectedThumbnail->size = 144;
        $expectedThumbnail->layers = [$expectedLayer];

        $expectedResult = new Icon();
        $expectedResult->id = $thumbnailId;
        $expectedResult->size = 144;
        $expectedResult->layers = [$expectedLayer];

        $this->hashCalculator->expects($this->once())
                             ->method('hashIcon')
                             ->with($this->equalTo($expectedThumbnail))
                             ->willReturn($thumbnailId);

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

        $this->hashCalculator->expects($this->never())
                             ->method('hashIcon');

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

        $this->modFileManager->expects($this->once())
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

        $this->modFileManager->expects($this->once())
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

        $this->modFileManager->expects($this->once())
                             ->method('readFile')
                             ->with($this->identicalTo($modName), $this->identicalTo('thumbnail.png'))
                             ->willReturn($content);

        $instance = $this->createInstance();
        $result = $this->invokeMethod($instance, 'getThumbnailSize', $mod);

        $this->assertSame($expectedResult, $result);
    }
}
