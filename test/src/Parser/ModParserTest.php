<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Parser;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\Entity\InfoJson;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Exception\FileNotFoundInModException;
use FactorioItemBrowser\Export\Helper\HashCalculator;
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
 * @coversDefaultClass \FactorioItemBrowser\Export\Parser\ModParser
 */
class ModParserTest extends TestCase
{
    use ReflectionTrait;

    /** @var HashCalculator&MockObject */
    private HashCalculator $hashCalculator;
    /** @var ModFileService&MockObject */
    private ModFileService $modFileManager;
    /** @var TranslationParser&MockObject */
    private TranslationParser $translationParser;

    protected function setUp(): void
    {
        $this->hashCalculator = $this->createMock(HashCalculator::class);
        $this->modFileManager = $this->createMock(ModFileService::class);
        $this->translationParser = $this->createMock(TranslationParser::class);
    }

    /**
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $parser = new ModParser($this->hashCalculator, $this->modFileManager, $this->translationParser);

        $this->assertSame($this->hashCalculator, $this->extractProperty($parser, 'hashCalculator'));
        $this->assertSame($this->modFileManager, $this->extractProperty($parser, 'modFileManager'));
        $this->assertSame($this->translationParser, $this->extractProperty($parser, 'translationParser'));
    }

    /**
     * @throws ExportException
     * @covers ::prepare
     */
    public function testPrepare(): void
    {
        /* @var Dump&MockObject $dump */
        $dump = $this->createMock(Dump::class);

        $parser = new ModParser($this->hashCalculator, $this->modFileManager, $this->translationParser);
        $parser->prepare($dump);

        $this->addToAssertionCount(1);
    }

    /**
     * Tests the parse method.
     * @throws ExportException
     * @covers ::parse
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

        $parser = $this->getMockBuilder(ModParser::class)
                       ->onlyMethods(['createMod', 'createThumbnail'])
                       ->setConstructorArgs([$this->hashCalculator, $this->modFileManager, $this->translationParser])
                       ->getMock();
        $parser->expects($this->exactly(2))
               ->method('createMod')
               ->withConsecutive(
                   [$this->identicalTo('abc')],
                   [$this->identicalTo('def')]
               )
               ->willReturnOnConsecutiveCalls(
                   $mod1,
                   $mod2
               );
        $parser->expects($this->exactly(2))
               ->method('createThumbnail')
               ->withConsecutive(
                   [$this->identicalTo($mod1)],
                   [$this->identicalTo($mod2)]
               )
               ->willReturnOnConsecutiveCalls(
                   $thumbnail,
                   null
               );

        $parser->parse($dump, $exportData);
    }

    /**
     * @throws ReflectionException
     * @covers ::createMod
     */
    public function testCreateMod(): void
    {
        $modName = 'abc';
        $expectedTitleTranslation = ['mod-name.abc'];
        $expectedDescriptionTranslation = ['mod-description.abc'];

        $infoJson = new InfoJson();
        $infoJson->setVersion('1.2.3')
                 ->setAuthor('def')
                 ->setTitle('ghi')
                 ->setDescription('jkl');

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

        $parser = new ModParser($this->hashCalculator, $this->modFileManager, $this->translationParser);
        $result = $this->invokeMethod($parser, 'createMod', $modName);

        $this->assertEquals($expectedMod, $result);
    }

    /**
     * @throws ReflectionException
     * @covers ::createThumbnail
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

        $parser = $this->getMockBuilder(ModParser::class)
                       ->onlyMethods(['getThumbnailSize'])
                       ->setConstructorArgs([$this->hashCalculator, $this->modFileManager, $this->translationParser])
                       ->getMock();
        $parser->expects($this->once())
               ->method('getThumbnailSize')
               ->with($this->identicalTo($mod))
               ->willReturn($size);

        $result = $this->invokeMethod($parser, 'createThumbnail', $mod);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @throws ReflectionException
     * @covers ::createThumbnail
     */
    public function testCreateThumbnailWithoutSize(): void
    {
        $modName = 'abc';

        $mod = new Mod();
        $mod->name = $modName;

        $this->hashCalculator->expects($this->never())
                             ->method('hashIcon');

        $parser = $this->getMockBuilder(ModParser::class)
                       ->onlyMethods(['getThumbnailSize'])
                       ->setConstructorArgs([$this->hashCalculator, $this->modFileManager, $this->translationParser])
                       ->getMock();
        $parser->expects($this->once())
               ->method('getThumbnailSize')
               ->with($this->identicalTo($mod))
               ->willReturn(0);

        $result = $this->invokeMethod($parser, 'createThumbnail', $mod);

        $this->assertNull($result);
    }

    /**
     * @throws ReflectionException
     * @covers ::getThumbnailSize
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

        $parser = new ModParser($this->hashCalculator, $this->modFileManager, $this->translationParser);
        $result = $this->invokeMethod($parser, 'getThumbnailSize', $mod);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * @throws ReflectionException
     * @covers ::getThumbnailSize
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

        $parser = new ModParser($this->hashCalculator, $this->modFileManager, $this->translationParser);
        $result = $this->invokeMethod($parser, 'getThumbnailSize', $mod);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * @throws ReflectionException
     * @covers ::getThumbnailSize
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

        $parser = new ModParser($this->hashCalculator, $this->modFileManager, $this->translationParser);
        $result = $this->invokeMethod($parser, 'getThumbnailSize', $mod);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * @throws ExportException
     * @covers ::validate
     */
    public function testValidate(): void
    {
        $exportData = $this->createMock(ExportData::class);

        $parser = new ModParser($this->hashCalculator, $this->modFileManager, $this->translationParser);
        $parser->validate($exportData);

        $this->addToAssertionCount(1);
    }
}
