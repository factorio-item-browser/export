<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Parser;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Common\Constant\EntityType;
use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\Entity\Dump\Fluid as DumpFluid;
use FactorioItemBrowser\Export\Entity\Dump\Item as DumpItem;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Parser\IconParser;
use FactorioItemBrowser\Export\Parser\ItemParser;
use FactorioItemBrowser\Export\Parser\TranslationParser;
use FactorioItemBrowser\ExportData\Entity\Combination;
use FactorioItemBrowser\ExportData\Entity\Item as ExportItem;
use FactorioItemBrowser\ExportData\Entity\LocalisedString;
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

    /** @var IconParser&MockObject */
    protected IconParser $iconParser;
    /** @var TranslationParser&MockObject */
    protected TranslationParser $translationParser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->iconParser = $this->createMock(IconParser::class);
        $this->translationParser = $this->createMock(TranslationParser::class);
    }

    /**
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $parser = new ItemParser($this->iconParser, $this->translationParser);

        $this->assertSame($this->iconParser, $this->extractProperty($parser, 'iconParser'));
        $this->assertSame($this->translationParser, $this->extractProperty($parser, 'translationParser'));
    }

    /**
     * @throws ExportException
     * @covers ::prepare
     */
    public function testPrepare(): void
    {
        $dump = $this->createMock(Dump::class);

        $parser = new ItemParser($this->iconParser, $this->translationParser);
        $parser->prepare($dump);

        $this->addToAssertionCount(1);
    }

    /**
     * @throws ExportException
     * @covers ::parse
     */
    public function testParse(): void
    {
        $dumpItem1 = $this->createMock(DumpItem::class);
        $dumpItem2 = $this->createMock(DumpItem::class);
        $dumpFluid1 = $this->createMock(DumpFluid::class);
        $dumpFluid2 = $this->createMock(DumpFluid::class);

        $dump = new Dump();
        $dump->items = [$dumpItem1, $dumpItem2];
        $dump->fluids = [$dumpFluid1, $dumpFluid2];

        $exportItem1 = $this->createMock(ExportItem::class);
        $exportItem2 = $this->createMock(ExportItem::class);
        $exportFluid1 = $this->createMock(ExportItem::class);
        $exportFluid2 = $this->createMock(ExportItem::class);

        $combination = $this->createMock(Combination::class);
        $combination->expects($this->exactly(4))
                    ->method('addItem')
                    ->withConsecutive(
                        [$this->identicalTo($exportItem1)],
                        [$this->identicalTo($exportItem2)],
                        [$this->identicalTo($exportFluid1)],
                        [$this->identicalTo($exportFluid2)]
                    );

        $parser = $this->getMockBuilder(ItemParser::class)
                       ->onlyMethods(['mapItem', 'mapFluid'])
                       ->setConstructorArgs([$this->iconParser, $this->translationParser])
                       ->getMock();
        $parser->expects($this->exactly(2))
               ->method('mapItem')
               ->withConsecutive(
                   [$this->identicalTo($dumpItem1)],
                   [$this->identicalTo($dumpItem2)]
               )
               ->willReturnOnConsecutiveCalls(
                   $exportItem1,
                   $exportItem2
               );
        $parser->expects($this->exactly(2))
               ->method('mapFluid')
               ->withConsecutive(
                   [$this->identicalTo($dumpFluid1)],
                   [$this->identicalTo($dumpFluid2)]
               )
               ->willReturnOnConsecutiveCalls(
                   $exportFluid1,
                   $exportFluid2
               );

        $parser->parse($dump, $combination);
    }

    /**
     * @throws ReflectionException
     * @covers ::mapItem
     */
    public function testMapItem(): void
    {
        $iconId = 'abc';

        $dumpItem = new DumpItem();
        $dumpItem->name = 'def';
        $dumpItem->localisedName = 'ghi';
        $dumpItem->localisedEntityName = 'jkl';
        $dumpItem->localisedDescription = 'mno';
        $dumpItem->localisedEntityDescription = 'pqr';

        $expectedResult = new ExportItem();
        $expectedResult->setType(EntityType::ITEM)
                       ->setName('def')
                       ->setIconId('abc');

        $this->iconParser->expects($this->once())
                         ->method('getIconId')
                         ->with($this->identicalTo(EntityType::ITEM), $this->identicalTo('def'))
                         ->willReturn($iconId);

        $this->translationParser->expects($this->exactly(2))
                                ->method('translate')
                                ->withConsecutive(
                                    [
                                        $this->isInstanceOf(LocalisedString::class),
                                        $this->identicalTo($dumpItem->localisedName),
                                        $this->identicalTo($dumpItem->localisedEntityName),
                                    ],
                                    [
                                        $this->isInstanceOf(LocalisedString::class),
                                        $this->identicalTo($dumpItem->localisedDescription),
                                        $this->identicalTo($dumpItem->localisedEntityDescription),
                                    ],
                                );

        $parser = new ItemParser($this->iconParser, $this->translationParser);
        $result = $this->invokeMethod($parser, 'mapItem', $dumpItem);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @throws ReflectionException
     * @covers ::mapFluid
     */
    public function testMapFluid(): void
    {
        $iconId = 'abc';

        $dumpFluid = new DumpFluid();
        $dumpFluid->name = 'def';
        $dumpFluid->localisedName = 'ghi';
        $dumpFluid->localisedDescription = 'jkl';

        $expectedResult = new ExportItem();
        $expectedResult->setType(EntityType::FLUID)
                       ->setName('def')
                       ->setIconId('abc');

        $this->iconParser->expects($this->once())
                         ->method('getIconId')
                         ->with($this->identicalTo(EntityType::FLUID), $this->identicalTo('def'))
                         ->willReturn($iconId);

        $this->translationParser->expects($this->exactly(2))
                                ->method('translate')
                                ->withConsecutive(
                                    [
                                        $this->isInstanceOf(LocalisedString::class),
                                        $this->identicalTo($dumpFluid->localisedName),
                                        $this->isNull(),
                                    ],
                                    [
                                        $this->isInstanceOf(LocalisedString::class),
                                        $this->identicalTo($dumpFluid->localisedDescription),
                                        $this->isNull(),
                                    ],
                                );

        $parser = new ItemParser($this->iconParser, $this->translationParser);
        $result = $this->invokeMethod($parser, 'mapFluid', $dumpFluid);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @throws ExportException
     * @covers ::validate
     */
    public function testValidate(): void
    {
        $combination = $this->createMock(Combination::class);

        $parser = new ItemParser($this->iconParser, $this->translationParser);
        $parser->validate($combination);

        $this->addToAssertionCount(1);
    }
}
