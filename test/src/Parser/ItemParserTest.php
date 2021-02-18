<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Parser;

use BluePsyduck\MapperManager\MapperManagerInterface;
use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Common\Constant\EntityType;
use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\Entity\Dump\Fluid as DumpFluid;
use FactorioItemBrowser\Export\Entity\Dump\Item as DumpItem;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Output\Console;
use FactorioItemBrowser\Export\Parser\IconParser;
use FactorioItemBrowser\Export\Parser\ItemParser;
use FactorioItemBrowser\Export\Parser\TranslationParser;
use FactorioItemBrowser\ExportData\Collection\ChunkedCollection;
use FactorioItemBrowser\ExportData\Entity\Item as ExportItem;
use FactorioItemBrowser\ExportData\ExportData;
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

    /** @var Console&MockObject */
    private Console $console;
    /** @var IconParser&MockObject */
    private IconParser $iconParser;
    /** @var MapperManagerInterface&MockObject */
    private MapperManagerInterface $mapperManager;
    /** @var TranslationParser&MockObject */
    private TranslationParser $translationParser;

    protected function setUp(): void
    {
        $this->console = $this->createMock(Console::class);
        $this->iconParser = $this->createMock(IconParser::class);
        $this->mapperManager = $this->createMock(MapperManagerInterface::class);
        $this->translationParser = $this->createMock(TranslationParser::class);
    }

    /**
     * @param array<string> $methods
     * @return ItemParser&MockObject
     */
    private function createInstance(array $methods = []): ItemParser
    {
        return $this->getMockBuilder(ItemParser::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($methods)
                    ->setConstructorArgs([
                        $this->console,
                        $this->iconParser,
                        $this->mapperManager,
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

        $items = $this->createMock(ChunkedCollection::class);
        $items->expects($this->exactly(4))
              ->method('add')
              ->withConsecutive(
                  [$this->identicalTo($exportItem1)],
                  [$this->identicalTo($exportItem2)],
                  [$this->identicalTo($exportFluid1)],
                  [$this->identicalTo($exportFluid2)],
              );

        $exportData = $this->createMock(ExportData::class);
        $exportData->expects($this->any())
                   ->method('getItems')
                   ->willReturn($items);

        $this->console->expects($this->exactly(2))
                      ->method('iterateWithProgressbar')
                      ->withConsecutive(
                          [$this->isType('string'), $this->identicalTo([$dumpItem1, $dumpItem2])],
                          [$this->isType('string'), $this->identicalTo([$dumpFluid1, $dumpFluid2])],
                      )
                      ->willReturnOnConsecutiveCalls(
                          $this->returnCallback(fn () => yield from [$dumpItem1, $dumpItem2]),
                          $this->returnCallback(fn () => yield from [$dumpFluid1, $dumpFluid2]),
                      );

        $instance = $this->createInstance(['createItem', 'createFluid']);
        $instance->expects($this->exactly(2))
                 ->method('createItem')
                 ->withConsecutive(
                     [$this->identicalTo($dumpItem1)],
                     [$this->identicalTo($dumpItem2)]
                 )
                 ->willReturnOnConsecutiveCalls(
                     $exportItem1,
                     $exportItem2
                 );
        $instance->expects($this->exactly(2))
                 ->method('createFluid')
                 ->withConsecutive(
                     [$this->identicalTo($dumpFluid1)],
                     [$this->identicalTo($dumpFluid2)]
                 )
                 ->willReturnOnConsecutiveCalls(
                     $exportFluid1,
                     $exportFluid2
                 );

        $instance->parse($dump, $exportData);
    }

    /**
     * @throws ReflectionException
     */
    public function testCreateItem(): void
    {
        $iconId = 'abc';

        $dumpItem = new DumpItem();
        $dumpItem->name = 'def';
        $dumpItem->localisedName = 'ghi';
        $dumpItem->localisedEntityName = 'jkl';
        $dumpItem->localisedDescription = 'mno';
        $dumpItem->localisedEntityDescription = 'pqr';

        $exportItem = new ExportItem();
        $exportItem->name = 'def';

        $expectedResult = new ExportItem();
        $expectedResult->name = 'def';
        $expectedResult->iconId = 'abc';

        $this->iconParser->expects($this->once())
                         ->method('getIconId')
                         ->with($this->identicalTo(EntityType::ITEM), $this->identicalTo('def'))
                         ->willReturn($iconId);

        $this->mapperManager->expects($this->once())
                            ->method('map')
                            ->with($this->identicalTo($dumpItem), $this->isInstanceOf(ExportItem::class))
                            ->willReturn($exportItem);

        $this->translationParser->expects($this->exactly(2))
                                ->method('translate')
                                ->withConsecutive(
                                    [
                                        $this->identicalTo($exportItem->labels),
                                        $this->identicalTo($dumpItem->localisedName),
                                        $this->identicalTo($dumpItem->localisedEntityName),
                                    ],
                                    [
                                        $this->identicalTo($exportItem->descriptions),
                                        $this->identicalTo($dumpItem->localisedDescription),
                                        $this->identicalTo($dumpItem->localisedEntityDescription),
                                    ],
                                );

        $instance = $this->createInstance();
        $result = $this->invokeMethod($instance, 'createItem', $dumpItem);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @throws ReflectionException
     */
    public function testCreateFluid(): void
    {
        $iconId = 'abc';

        $dumpFluid = new DumpFluid();
        $dumpFluid->name = 'def';
        $dumpFluid->localisedName = 'ghi';
        $dumpFluid->localisedDescription = 'jkl';

        $exportItem = new ExportItem();
        $exportItem->name = 'def';

        $expectedResult = new ExportItem();
        $expectedResult->name = 'def';
        $expectedResult->iconId = 'abc';

        $this->iconParser->expects($this->once())
                         ->method('getIconId')
                         ->with($this->identicalTo(EntityType::FLUID), $this->identicalTo('def'))
                         ->willReturn($iconId);

        $this->mapperManager->expects($this->once())
                            ->method('map')
                            ->with($this->identicalTo($dumpFluid), $this->isInstanceOf(ExportItem::class))
                            ->willReturn($exportItem);

        $this->translationParser->expects($this->exactly(2))
                                ->method('translate')
                                ->withConsecutive(
                                    [
                                        $this->identicalTo($exportItem->labels),
                                        $this->identicalTo($dumpFluid->localisedName),
                                        $this->isNull(),
                                    ],
                                    [
                                        $this->identicalTo($exportItem->descriptions),
                                        $this->identicalTo($dumpFluid->localisedDescription),
                                        $this->isNull(),
                                    ],
                                );

        $instance = $this->createInstance();
        $result = $this->invokeMethod($instance, 'createFluid', $dumpFluid);

        $this->assertEquals($expectedResult, $result);
    }
}
