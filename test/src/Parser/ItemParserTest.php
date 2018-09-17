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
     * @throws ReflectionException
     * @covers ::parse
     */
    public function testParse(): void
    {
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

        $item1 = new Item();
        $item1->setType('abc')
              ->setName('def');
        $item2 = new Item();
        $item2->setType('ghi')
              ->setName('jkl');
        $item3 = new Item();
        $item3->setType('mno')
              ->setName('pqr');
        $item4 = new Item();
        $item4->setType('stu')
              ->setName('vwx');

        $expectedParsedItems = [
            'abc|def' => $item1,
            'ghi|jkl' => $item2,
            'mno|pqr' => $item3,
            'stu|vwx' => $item4,
        ];

        /* @var ItemParser|MockObject $parser */
        $parser = $this->getMockBuilder(ItemParser::class)
                       ->setMethods(['parseItem'])
                       ->disableOriginalConstructor()
                       ->getMock();
        $parser->expects($this->exactly(4))
               ->method('parseItem')
               ->withConsecutive(
                   [$this->equalTo(new DataContainer(['abc' => 'def'])), 'item'],
                   [$this->equalTo(new DataContainer(['ghi' => 'jkl'])), 'item'],
                   [$this->equalTo(new DataContainer(['mno' => 'pqr'])), 'fluid'],
                   [$this->equalTo(new DataContainer(['stu' => 'vwx'])), 'fluid']
               )
               ->willReturnOnConsecutiveCalls(
                   $item1,
                   $item2,
                   $item3,
                   $item4
               );
        $this->injectProperty($parser, 'parsedItems', ['fail' => new Item()]);

        $parser->parse($dumpData);
        $this->assertEquals($expectedParsedItems, $this->extractProperty($parser, 'parsedItems'));
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
        
        /* @var ItemParser|MockObject $parser */
        $parser = $this->getMockBuilder(ItemParser::class)
                       ->setMethods(['addTranslations'])
                       ->disableOriginalConstructor()
                       ->getMock();
        $parser->expects($this->once())
               ->method('addTranslations')
               ->with($this->isInstanceOf(Item::class), $itemData);

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
     * Tests the check method.
     * @throws ReflectionException
     * @covers ::check
     */
    public function testCheck(): void
    {
        $item1 = (new Item())->setName('abc');
        $item2 = (new Item())->setName('def');
        $parsedItems = [$item1, $item2];

        /* @var ItemParser|MockObject $parser */
        $parser = $this->getMockBuilder(ItemParser::class)
                       ->setMethods(['checkIcon'])
                       ->disableOriginalConstructor()
                       ->getMock();
        $parser->expects($this->exactly(2))
               ->method('checkIcon')
               ->withConsecutive(
                   [$item1],
                   [$item2]
               );

        $this->injectProperty($parser, 'parsedItems', $parsedItems);
        $parser->check();
    }

    /**
     * Provides the data for the checkIcon test.
     * @return array
     */
    public function provideCheckIcon(): array
    {
        return [
            ['foo', true],
            [null, false],
        ];
    }

    /**
     * Tests the checkIcon method.
     * @param null|string $resultHash
     * @param bool $expectSet
     * @throws ReflectionException
     * @covers ::checkIcon
     * @dataProvider provideCheckIcon
     */
    public function testCheckIcon(?string $resultHash, bool $expectSet): void
    {
        $type = 'abc';
        $name = 'def';

        /* @var IconParser|MockObject $iconParser */
        $iconParser = $this->getMockBuilder(IconParser::class)
                           ->setMethods(['getIconHashForEntity'])
                           ->disableOriginalConstructor()
                           ->getMock();
        $iconParser->expects($this->once())
                   ->method('getIconHashForEntity')
                   ->with($type, $name)
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
        $this->invokeMethod($parser, 'checkIcon', $item);
    }

    /**
     * Tests the persist method.
     * @throws ReflectionException
     * @covers ::persist
     */
    public function testPersist(): void
    {
        $item1 = (new Item())->setName('abc');
        $item2 = (new Item())->setName('def');
        $parsedItems = [$item1, $item2];
        $itemHash1 = 'ghi';
        $itemHash2 = 'jkl';
        $expectedItemHashes = [$itemHash1, $itemHash2];

        /* @var EntityRegistry|MockObject $itemRegistry */
        $itemRegistry = $this->getMockBuilder(EntityRegistry::class)
                             ->setMethods(['set'])
                             ->disableOriginalConstructor()
                             ->getMock();
        $itemRegistry->expects($this->exactly(2))
                     ->method('set')
                     ->withConsecutive(
                         [$item1],
                         [$item2]
                     )
                     ->willReturnOnConsecutiveCalls(
                         $itemHash1,
                         $itemHash2
                     );

        /* @var Combination|MockObject $combination */
        $combination = $this->getMockBuilder(Combination::class)
                            ->setMethods(['setItemHashes'])
                            ->disableOriginalConstructor()
                            ->getMock();
        $combination->expects($this->once())
                    ->method('setItemHashes')
                    ->with($expectedItemHashes);

        /* @var IconParser $iconParser */
        $iconParser = $this->createMock(IconParser::class);
        /* @var Translator $translator */
        $translator = $this->createMock(Translator::class);

        $parser = new ItemParser($iconParser, $itemRegistry, $translator);
        $this->injectProperty($parser, 'parsedItems', $parsedItems);

        $parser->persist($combination);
    }
}
