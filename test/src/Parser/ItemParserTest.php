<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Parser;

use BluePsyduck\Common\Data\DataContainer;
use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Export\I18n\Translator;
use FactorioItemBrowser\Export\Parser\IconParser;
use FactorioItemBrowser\Export\Parser\ItemParser;
use FactorioItemBrowser\ExportData\Entity\Item;
use FactorioItemBrowser\ExportData\Entity\LocalisedString;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;
use FactorioItemBrowser\ExportData\Registry\EntityRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the ItemParser class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Parser\ItemParser
 */
class ItemParserTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests the constructing.
     * @covers ::__construct
     * @throws ReflectionException
     */
    public function testConstruct(): void
    {
        /* @var IconParser $iconParser */
        $iconParser = $this->createMock(IconParser::class);
        /* @var EntityRegistry $itemRegistry */
        $itemRegistry = $this->createMock(EntityRegistry::class);
        /* @var Translator $translator */
        $translator = $this->createMock(Translator::class);

        $parser = new ItemParser($iconParser, $itemRegistry, $translator);
        $this->assertSame($iconParser, $this->extractProperty($parser, 'iconParser'));
        $this->assertSame($itemRegistry, $this->extractProperty($parser, 'itemRegistry'));
        $this->assertSame($translator, $this->extractProperty($parser, 'translator'));
    }

    /**
     * Tests the parse method.
     * @covers ::parse
     */
    public function testParse(): void
    {
        $combination = new Combination();
        $dumpData = new DataContainer([
            'items' => [
                ['abc' => 'def'],
                ['ghi' => 'jkl'],
            ],
            'fluids' => [
                ['mno' => 'pqr'],
                ['stu' => 'vwx'],
            ],
        ]);

        /* @var ItemParser|MockObject $parser */
        $parser = $this->getMockBuilder(ItemParser::class)
                       ->setMethods(['processItem'])
                       ->disableOriginalConstructor()
                       ->getMock();
        $parser->expects($this->exactly(4))
               ->method('processItem')
               ->withConsecutive(
                   [$combination, $this->equalTo(new DataContainer(['abc' => 'def'])), 'item'],
                   [$combination, $this->equalTo(new DataContainer(['ghi' => 'jkl'])), 'item'],
                   [$combination, $this->equalTo(new DataContainer(['mno' => 'pqr'])), 'fluid'],
                   [$combination, $this->equalTo(new DataContainer(['stu' => 'vwx'])), 'fluid']
               );
        $parser->parse($combination, $dumpData);
    }

    /**
     * Tests the processItem method.
     * @throws ReflectionException
     * @covers ::processItem
     */
    public function testProcessItem(): void
    {
        $itemData = new DataContainer(['abc' => 'def']);
        $type = 'ghi';
        $item = new Item();
        $itemHash = 'jkl';

        /* @var IconParser $iconParser */
        $iconParser = $this->createMock(IconParser::class);
        /* @var Translator $translator */
        $translator = $this->createMock(Translator::class);

        /* @var EntityRegistry|MockObject $itemRegistry */
        $itemRegistry = $this->getMockBuilder(EntityRegistry::class)
                             ->setMethods(['set'])
                             ->disableOriginalConstructor()
                             ->getMock();
        $itemRegistry->expects($this->once())
                     ->method('set')
                     ->with($item)
                     ->willReturn($itemHash);

        /* @var Combination|MockObject $combination */
        $combination = $this->getMockBuilder(Combination::class)
                            ->setMethods(['addItemHash'])
                            ->disableOriginalConstructor()
                            ->getMock();
        $combination->expects($this->once())
                    ->method('addItemHash')
                    ->with($itemHash);

        /* @var ItemParser|MockObject $parser */
        $parser = $this->getMockBuilder(ItemParser::class)
                       ->setMethods(['parseItem', 'addTranslations', 'assignIconHash'])
                       ->setConstructorArgs([$iconParser, $itemRegistry, $translator])
                       ->getMock();
        $parser->expects($this->once())
               ->method('parseItem')
               ->with($itemData, $type)
               ->willReturn($item);
        $parser->expects($this->once())
               ->method('addTranslations')
               ->with($item, $itemData);
        $parser->expects($this->once())
               ->method('assignIconHash')
               ->with($combination, $item);

        $this->invokeMethod($parser, 'processItem', $combination, $itemData, $type);
    }

    /**
     * Tests the parseItem method.
     * @covers ::parseItem
     * @throws ReflectionException
     */
    public function testParseItem(): void
    {
        $type = 'abc';
        $itemData = new DataContainer([
            'name' => 'Def'
        ]);
        $expectedResult = new Item();
        $expectedResult->setType('abc')
                       ->setName('def');
        
        /* @var IconParser $iconParser */
        $iconParser = $this->createMock(IconParser::class);
        /* @var EntityRegistry $itemRegistry */
        $itemRegistry = $this->createMock(EntityRegistry::class);
        /* @var Translator $translator */
        $translator = $this->createMock(Translator::class);
        
        $parser = new ItemParser($iconParser, $itemRegistry, $translator);
        $result = $this->invokeMethod($parser, 'parseItem', $itemData, $type);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the addTranslations method.
     * @throws ReflectionException
     * @covers ::addTranslations
     */
    public function testAddTranslations(): void
    {
        $labels = (new LocalisedString())->setTranslation('en', 'abc');
        $descriptions = (new LocalisedString())->setTranslation('en', 'def');

        $itemData = new DataContainer([
            'localised' => [
                'name' => ['ghi'],
                'entityName' => ['jkl'],
                'description' => ['mno'],
                'entityDescription' => ['pqr'],
            ]
        ]);

        /* @var Item|MockObject $item */
        $item = $this->getMockBuilder(Item::class)
                     ->setMethods(['getLabels', 'getDescriptions'])
                     ->disableOriginalConstructor()
                     ->getMock();
        $item->expects($this->once())
             ->method('getLabels')
             ->willReturn($labels);
        $item->expects($this->once())
             ->method('getDescriptions')
             ->willReturn($descriptions);

        /* @var Translator|MockObject $translator */
        $translator = $this->getMockBuilder(Translator::class)
                           ->setMethods(['addTranslationsToEntity'])
                           ->disableOriginalConstructor()
                           ->getMock();
        $translator->expects($this->exactly(2))
                   ->method('addTranslationsToEntity')
                   ->withConsecutive(
                       [$labels, 'name', ['ghi'], ['jkl']],
                       [$descriptions, 'description', ['mno'], ['pqr']]
                   );

        /* @var IconParser $iconParser */
        $iconParser = $this->createMock(IconParser::class);
        /* @var EntityRegistry $itemRegistry */
        $itemRegistry = $this->createMock(EntityRegistry::class);

        $parser = new ItemParser($iconParser, $itemRegistry, $translator);
        $this->invokeMethod($parser, 'addTranslations', $item, $itemData);
    }

    /**
     * Provides the data for the assignIconHash test.
     * @return array
     */
    public function provideAssignIconHash(): array
    {
        return [
            ['foo', true],
            [null, false],
        ];
    }

    /**
     * Tests the assignIconHash method.
     * @param null|string $resultHash
     * @param bool $expectSet
     * @throws ReflectionException
     * @covers ::assignIconHash
     * @dataProvider provideAssignIconHash
     */
    public function testAssignIconHash(?string $resultHash, bool $expectSet): void
    {
        $type = 'abc';
        $name = 'def';
        $combination = new Combination();

        /* @var IconParser|MockObject $iconParser */
        $iconParser = $this->getMockBuilder(IconParser::class)
                           ->setMethods(['getIconHashForEntity'])
                           ->disableOriginalConstructor()
                           ->getMock();
        $iconParser->expects($this->once())
                   ->method('getIconHashForEntity')
                   ->with($combination, $type, $name)
                   ->willReturn($resultHash);

        /* @var Item|MockObject $item */
        $item = $this->getMockBuilder(Item::class)
                     ->setMethods(['getType', 'getName', 'setIconHash'])
                     ->disableOriginalConstructor()
                     ->getMock();
        $item->expects($this->once())
             ->method('getType')
             ->willReturn($type);
        $item->expects($this->once())
             ->method('getName')
             ->willReturn($name);
        $item->expects($expectSet ? $this->once() : $this->never())
             ->method('setIconHash')
             ->with((string) $resultHash);

        /* @var EntityRegistry $itemRegistry */
        $itemRegistry = $this->createMock(EntityRegistry::class);
        /* @var Translator $translator */
        $translator = $this->createMock(Translator::class);

        $parser = new ItemParser($iconParser, $itemRegistry, $translator);
        $this->invokeMethod($parser, 'assignIconHash', $combination, $item);
    }
}
