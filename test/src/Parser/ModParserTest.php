<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Parser;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\Entity\InfoJson;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Exception\FileNotFoundInModException;
use FactorioItemBrowser\Export\Helper\HashCalculator;
use FactorioItemBrowser\Export\Mod\ModFileManager;
use FactorioItemBrowser\Export\Parser\ModParser;
use FactorioItemBrowser\Export\Parser\TranslationParser;
use FactorioItemBrowser\ExportData\Entity\Combination;
use FactorioItemBrowser\ExportData\Entity\Icon;
use FactorioItemBrowser\ExportData\Entity\Icon\Layer;
use FactorioItemBrowser\ExportData\Entity\LocalisedString;
use FactorioItemBrowser\ExportData\Entity\Mod;
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
    /** @var ModFileManager&MockObject */
    private ModFileManager $modFileManager;
    /** @var TranslationParser&MockObject */
    private TranslationParser $translationParser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hashCalculator = $this->createMock(HashCalculator::class);
        $this->modFileManager = $this->createMock(ModFileManager::class);
        $this->translationParser = $this->createMock(TranslationParser::class);
    }

    /**
     * Tests the constructing.
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
     * Tests the prepare method.
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

        $mod1 = $this->createMock(Mod::class);
        $mod1->expects($this->once())
             ->method('setThumbnailId')
             ->with($this->identicalTo($thumbnailId));

        $mod2 = $this->createMock(Mod::class);
        $mod2->expects($this->never())
             ->method('setThumbnailId');

        $dump = new Dump();
        $dump->modNames = $modNames;

        $thumbnail = $this->createMock(Icon::class);
        $thumbnail->expects($this->once())
                  ->method('getId')
                  ->willReturn($thumbnailId);

        $combination = $this->createMock(Combination::class);
        $combination->expects($this->exactly(2))
                    ->method('addMod')
                    ->withConsecutive(
                        [$this->identicalTo($mod1)],
                        [$this->identicalTo($mod2)]
                    );
        $combination->expects($this->once())
                    ->method('addIcon')
                    ->with($this->identicalTo($thumbnail));

        $parser = $this->getMockBuilder(ModParser::class)
                       ->onlyMethods(['mapMod', 'mapThumbnail'])
                       ->setConstructorArgs([$this->hashCalculator, $this->modFileManager, $this->translationParser])
                       ->getMock();
        $parser->expects($this->exactly(2))
               ->method('mapMod')
               ->withConsecutive(
                   [$this->identicalTo('abc')],
                   [$this->identicalTo('def')]
               )
               ->willReturnOnConsecutiveCalls(
                   $mod1,
                   $mod2
               );
        $parser->expects($this->exactly(2))
               ->method('mapThumbnail')
               ->withConsecutive(
                   [$this->identicalTo($mod1)],
                   [$this->identicalTo($mod2)]
               )
               ->willReturnOnConsecutiveCalls(
                   $thumbnail,
                   null
               );

        $parser->parse($dump, $combination);
    }

    /**
     * Tests the mapMod method.
     * @throws ReflectionException
     * @covers ::mapMod
     */
    public function testMapMod(): void
    {
        $modName = 'abc';
        $expectedTitleTranslation = ['mod-name.abc'];
        $expectedDescriptionTranslation = ['mod-description.abc'];

        $infoJson = new InfoJson();
        $infoJson->setVersion('1.2.3')
                 ->setAuthor('def')
                 ->setTitle('ghi')
                 ->setDescription('jkl');

        $expectedTitles = new LocalisedString();
        $expectedTitles->setTranslations(['en' => 'ghi']);

        $expectedDescriptions = new LocalisedString();
        $expectedDescriptions->setTranslations(['en' => 'jkl']);

        $expectedResult = new Mod();
        $expectedResult->setName('abc')
                       ->setVersion('1.2.3')
                       ->setAuthor('def')
                       ->setTitles($expectedTitles)
                       ->setDescriptions($expectedDescriptions);

        $this->modFileManager->expects($this->once())
                             ->method('getInfo')
                             ->with($this->identicalTo($modName))
                             ->willReturn($infoJson);

        $this->translationParser->expects($this->exactly(2))
                                ->method('translate')
                                ->withConsecutive(
                                    [
                                        $this->equalTo($expectedTitles),
                                        $this->identicalTo($expectedTitleTranslation),
                                    ],
                                    [
                                        $this->equalTo($expectedDescriptions),
                                        $this->identicalTo($expectedDescriptionTranslation),
                                    ],
                                );

        $parser = new ModParser($this->hashCalculator, $this->modFileManager, $this->translationParser);
        $result = $this->invokeMethod($parser, 'mapMod', $modName);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the mapThumbnail method.
     * @throws ReflectionException
     * @covers ::mapThumbnail
     */
    public function testMapThumbnail(): void
    {
        $modName = 'abc';
        $thumbnailId = 'def';
        $size = 32;

        $mod = new Mod();
        $mod->setName($modName);

        $expectedLayer = new Layer();
        $expectedLayer->setFileName('__abc__/thumbnail.png')
                      ->setSize($size);

        $expectedThumbnail = new Icon();
        $expectedThumbnail->setSize(144)
                          ->setLayers([$expectedLayer]);

        $expectedResult = new Icon();
        $expectedResult->setSize(144)
                       ->setLayers([$expectedLayer])
                       ->setId($thumbnailId);

        $this->hashCalculator->expects($this->once())
                             ->method('hashIcon')
                             ->with($this->equalTo($expectedThumbnail))
                             ->willReturn($thumbnailId);

        /* @var ModParser&MockObject $parser */
        $parser = $this->getMockBuilder(ModParser::class)
                       ->onlyMethods(['getThumbnailSize'])
                       ->setConstructorArgs([$this->hashCalculator, $this->modFileManager, $this->translationParser])
                       ->getMock();
        $parser->expects($this->once())
               ->method('getThumbnailSize')
               ->with($this->identicalTo($mod))
               ->willReturn($size);

        $result = $this->invokeMethod($parser, 'mapThumbnail', $mod);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the mapThumbnail method.
     * @throws ReflectionException
     * @covers ::mapThumbnail
     */
    public function testMapThumbnailWithoutSize(): void
    {
        $modName = 'abc';

        $mod = new Mod();
        $mod->setName($modName);

        $this->hashCalculator->expects($this->never())
                             ->method('hashIcon');

        /* @var ModParser&MockObject $parser */
        $parser = $this->getMockBuilder(ModParser::class)
                       ->onlyMethods(['getThumbnailSize'])
                       ->setConstructorArgs([$this->hashCalculator, $this->modFileManager, $this->translationParser])
                       ->getMock();
        $parser->expects($this->once())
               ->method('getThumbnailSize')
               ->with($this->identicalTo($mod))
               ->willReturn(0);

        $result = $this->invokeMethod($parser, 'mapThumbnail', $mod);

        $this->assertNull($result);
    }

    /**
     * Tests the getThumbnailSize method.
     * @throws ReflectionException
     * @covers ::getThumbnailSize
     */
    public function testGetThumbnailSize(): void
    {
        $modName = 'abc';
        $content = file_get_contents(__DIR__ . '/../../asset/icon.png');
        $expectedResult = 32;

        $mod = new Mod();
        $mod->setName($modName);

        $this->modFileManager->expects($this->once())
                             ->method('readFile')
                             ->with($this->identicalTo($modName), $this->identicalTo('thumbnail.png'))
                             ->willReturn($content);

        $parser = new ModParser($this->hashCalculator, $this->modFileManager, $this->translationParser);
        $result = $this->invokeMethod($parser, 'getThumbnailSize', $mod);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the getThumbnailSize method.
     * @throws ReflectionException
     * @covers ::getThumbnailSize
     */
    public function testGetThumbnailSizeWithoutThumbnail(): void
    {
        $modName = 'abc';
        $expectedResult = 0;

        $mod = new Mod();
        $mod->setName($modName);

        $this->modFileManager->expects($this->once())
                             ->method('readFile')
                             ->with($this->identicalTo($modName), $this->identicalTo('thumbnail.png'))
                             ->willThrowException($this->createMock(FileNotFoundInModException::class));

        $parser = new ModParser($this->hashCalculator, $this->modFileManager, $this->translationParser);
        $result = $this->invokeMethod($parser, 'getThumbnailSize', $mod);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the getThumbnailSize method.
     * @throws ReflectionException
     * @covers ::getThumbnailSize
     */
    public function testGetThumbnailSizeWithInvalidContent(): void
    {
        $modName = 'abc';
        $content = 'not-an-image';
        $expectedResult = 0;

        $mod = new Mod();
        $mod->setName($modName);

        $this->modFileManager->expects($this->once())
                             ->method('readFile')
                             ->with($this->identicalTo($modName), $this->identicalTo('thumbnail.png'))
                             ->willReturn($content);

        $parser = new ModParser($this->hashCalculator, $this->modFileManager, $this->translationParser);
        $result = $this->invokeMethod($parser, 'getThumbnailSize', $mod);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the validate method.
     * @throws ExportException
     * @covers ::validate
     */
    public function testValidate(): void
    {
        /* @var Combination&MockObject $combination */
        $combination = $this->createMock(Combination::class);

        $parser = new ModParser($this->hashCalculator, $this->modFileManager, $this->translationParser);
        $parser->validate($combination);

        $this->addToAssertionCount(1);
    }
}
