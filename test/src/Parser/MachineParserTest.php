<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Parser;

use BluePsyduck\MapperManager\MapperManagerInterface;
use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Common\Constant\EntityType;
use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\Entity\Dump\Machine as DumpMachine;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Parser\IconParser;
use FactorioItemBrowser\Export\Parser\MachineParser;
use FactorioItemBrowser\Export\Parser\TranslationParser;
use FactorioItemBrowser\ExportData\Collection\ChunkedCollection;
use FactorioItemBrowser\ExportData\Entity\Machine as ExportMachine;
use FactorioItemBrowser\ExportData\ExportData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the MachineParser class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Parser\MachineParser
 */
class MachineParserTest extends TestCase
{
    use ReflectionTrait;

    /** @var IconParser&MockObject */
    private IconParser $iconParser;
    /** @var MapperManagerInterface&MockObject */
    private MapperManagerInterface $mapperManager;
    /** @var TranslationParser&MockObject */
    private TranslationParser $translationParser;

    protected function setUp(): void
    {
        $this->iconParser = $this->createMock(IconParser::class);
        $this->mapperManager = $this->createMock(MapperManagerInterface::class);
        $this->translationParser = $this->createMock(TranslationParser::class);
    }

    /**
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $parser = new MachineParser($this->iconParser, $this->mapperManager, $this->translationParser);

        $this->assertSame($this->iconParser, $this->extractProperty($parser, 'iconParser'));
        $this->assertSame($this->mapperManager, $this->extractProperty($parser, 'mapperManager'));
        $this->assertSame($this->translationParser, $this->extractProperty($parser, 'translationParser'));
    }

    /**
     * @throws ExportException
     * @covers ::prepare
     */
    public function testPrepare(): void
    {
        $dump = $this->createMock(Dump::class);

        $parser = new MachineParser($this->iconParser, $this->mapperManager, $this->translationParser);
        $parser->prepare($dump);

        $this->addToAssertionCount(1);
    }

    /**
     * @throws ExportException
     * @covers ::parse
     */
    public function testParse(): void
    {
        $dumpMachine1 = $this->createMock(DumpMachine::class);
        $dumpMachine2 = $this->createMock(DumpMachine::class);
        $exportMachine1 = $this->createMock(ExportMachine::class);
        $exportMachine2 = $this->createMock(ExportMachine::class);

        $dump = new Dump();
        $dump->machines = [$dumpMachine1, $dumpMachine2];

        $machines = $this->createMock(ChunkedCollection::class);
        $machines->expects($this->exactly(2))
                 ->method('add')
                 ->withConsecutive(
                     [$this->identicalTo($exportMachine1)],
                     [$this->identicalTo($exportMachine2)],
                 );

        $exportData = $this->createMock(ExportData::class);
        $exportData->expects($this->any())
                   ->method('getMachines')
                   ->willReturn($machines);

        $parser = $this->getMockBuilder(MachineParser::class)
                       ->onlyMethods(['createMachine'])
                       ->setConstructorArgs([$this->iconParser, $this->mapperManager, $this->translationParser])
                       ->getMock();
        $parser->expects($this->exactly(2))
               ->method('createMachine')
               ->withConsecutive(
                   [$this->identicalTo($dumpMachine1)],
                   [$this->identicalTo($dumpMachine2)]
               )
               ->willReturnOnConsecutiveCalls(
                   $exportMachine1,
                   $exportMachine2
               );

        $parser->parse($dump, $exportData);
    }

    /**
     * @throws ReflectionException
     * @covers ::createMachine
     */
    public function testCreateMachine(): void
    {
        $iconId = 'abc';

        $dumpMachine = new DumpMachine();
        $dumpMachine->name = 'def';
        $dumpMachine->localisedName = 'ghi';
        $dumpMachine->localisedDescription = 'jkl';

        $exportMachine = new ExportMachine();
        $exportMachine->name = 'def';

        $expectedResult = new ExportMachine();
        $expectedResult->name = 'def';
        $expectedResult->iconId = $iconId;

        $this->iconParser->expects($this->once())
                         ->method('getIconId')
                         ->with(
                             $this->identicalTo(EntityType::MACHINE),
                             $this->identicalTo('def')
                         )
                         ->willReturn($iconId);

        $this->mapperManager->expects($this->once())
                            ->method('map')
                            ->with($this->identicalTo($dumpMachine), $this->isInstanceOf(ExportMachine::class))
                            ->willReturn($exportMachine);

        $this->translationParser->expects($this->exactly(2))
                                ->method('translate')
                                ->withConsecutive(
                                    [
                                        $this->identicalTo($exportMachine->labels),
                                        $this->identicalTo('ghi'),
                                        $this->isNull(),
                                    ],
                                    [
                                        $this->identicalTo($exportMachine->descriptions),
                                        $this->identicalTo('jkl'),
                                        $this->isNull(),
                                    ],
                                );

        $parser = new MachineParser($this->iconParser, $this->mapperManager, $this->translationParser);
        $result = $this->invokeMethod($parser, 'createMachine', $dumpMachine);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @throws ExportException
     * @covers ::validate
     */
    public function testValidate(): void
    {
        $exportData = $this->createMock(ExportData::class);

        $parser = new MachineParser($this->iconParser, $this->mapperManager, $this->translationParser);
        $parser->validate($exportData);

        $this->addToAssertionCount(1);
    }
}
