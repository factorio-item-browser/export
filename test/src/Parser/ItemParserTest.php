<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Parser;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Common\Constant\EntityType;
use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\Entity\Dump\Fluid as DumpFluid;
use FactorioItemBrowser\Export\Entity\Dump\Item as DumpItem;
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

    /**
     * The mocked icon parser.
     * @var IconParser&MockObject
     */
    protected $iconParser;

    /**
     * The mocked translation parser.
     * @var TranslationParser&MockObject
     */
    protected $translationParser;

    /**
     * Sets up the test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->iconParser = $this->createMock(IconParser::class);
        $this->translationParser = $this->createMock(TranslationParser::class);
    }

    /**
     * Tests the constructing.
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
     * Tests the prepare method.
     * @covers ::prepare
     */
    public function testPrepare(): void
    {
        /* @var Dump&MockObject $dump */
        $dump = $this->createMock(Dump::class);

        $parser = new ItemParser($this->iconParser, $this->translationParser);
        $parser->prepare($dump);

        $this->addToAssertionCount(1);
    }

    /**
     * Tests the parse method.
     * @covers ::parse
     */
    public function testParse(): void
    {
        /* @var DumpItem&MockObject $dumpItem1 */
        $dumpItem1 = $this->createMock(DumpItem::class);
        /* @var DumpItem&MockObject $dumpItem2 */
        $dumpItem2 = $this->createMock(DumpItem::class);
        /* @var DumpFluid&MockObject $dumpFluid1 */
        $dumpFluid1 = $this->createMock(DumpFluid::class);
        /* @var DumpFluid&MockObject $dumpFluid2 */
        $dumpFluid2 = $this->createMock(DumpFluid::class);
        
        $dump = new Dump();
        $dump->getControlStage()->setItems([$dumpItem1, $dumpItem2])
                                ->setFluids([$dumpFluid1, $dumpFluid2]);

        /* @var ExportItem&MockObject $exportItem1 */
        $exportItem1 = $this->createMock(ExportItem::class);
        /* @var ExportItem&MockObject $exportItem2 */
        $exportItem2 = $this->createMock(ExportItem::class);
        /* @var ExportItem&MockObject $exportFluid1 */
        $exportFluid1 = $this->createMock(ExportItem::class);
        /* @var ExportItem&MockObject $exportFluid2 */
        $exportFluid2 = $this->createMock(ExportItem::class);
        
        /* @var Combination&MockObject $combination */
        $combination = $this->createMock(Combination::class);
        $combination->expects($this->exactly(4))
                    ->method('addItem')
                    ->withConsecutive(
                        [$this->identicalTo($exportItem1)],
                        [$this->identicalTo($exportItem2)],
                        [$this->identicalTo($exportFluid1)],
                        [$this->identicalTo($exportFluid2)]
                    );
     
        /* @var ItemParser&MockObject $parser */
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
     * Tests the mapItem method.
     * @throws ReflectionException
     * @covers ::mapItem
     */
    public function testMapItem(): void
    {
        $iconId = 'abc';

        $dumpItem = new DumpItem();
        $dumpItem->setName('Def');

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
                                        $this->identicalTo($dumpItem->getLocalisedName()),
                                        $this->identicalTo($dumpItem->getLocalisedEntityName()),
                                    ],
                                    [
                                        $this->isInstanceOf(LocalisedString::class),
                                        $this->identicalTo($dumpItem->getLocalisedDescription()),
                                        $this->identicalTo($dumpItem->getLocalisedEntityDescription()),
                                    ],
                                );

        $parser = new ItemParser($this->iconParser, $this->translationParser);
        $result = $this->invokeMethod($parser, 'mapItem', $dumpItem);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the mapFluid method.
     * @throws ReflectionException
     * @covers ::mapFluid
     */
    public function testMapFluid(): void
    {
        $iconId = 'abc';

        $dumpFluid = new DumpFluid();
        $dumpFluid->setName('Def');

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
                                        $this->identicalTo($dumpFluid->getLocalisedName()),
                                        $this->isNull(),
                                    ],
                                    [
                                        $this->isInstanceOf(LocalisedString::class),
                                        $this->identicalTo($dumpFluid->getLocalisedDescription()),
                                        $this->isNull(),
                                    ],
                                );

        $parser = new ItemParser($this->iconParser, $this->translationParser);
        $result = $this->invokeMethod($parser, 'mapFluid', $dumpFluid);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the validate method.
     * @covers ::validate
     */
    public function testValidate(): void
    {
        /* @var Combination&MockObject $combination */
        $combination = $this->createMock(Combination::class);

        $parser = new ItemParser($this->iconParser, $this->translationParser);
        $parser->validate($combination);

        $this->addToAssertionCount(1);
    }
}
