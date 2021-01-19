<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Parser;

use BluePsyduck\MapperManager\MapperManagerInterface;
use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Common\Constant\EntityType;
use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\Entity\Dump\Machine as DumpMachine;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Output\Console;
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
 * @covers \FactorioItemBrowser\Export\Parser\MachineParser
 */
class MachineParserTest extends TestCase
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
     * @return MachineParser&MockObject
     */
    private function createInstance(array $methods = []): MachineParser
    {
        return $this->getMockBuilder(MachineParser::class)
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

        $this->console->expects($this->once())
                      ->method('iterateWithProgressbar')
                      ->with($this->isType('string'), $this->identicalTo([$dumpMachine1, $dumpMachine2]))
                      ->willReturnCallback(fn () => yield from [$dumpMachine1, $dumpMachine2]);

        $instance = $this->createInstance(['createMachine']);
        $instance->expects($this->exactly(2))
                 ->method('createMachine')
                 ->withConsecutive(
                     [$this->identicalTo($dumpMachine1)],
                     [$this->identicalTo($dumpMachine2)]
                 )
                 ->willReturnOnConsecutiveCalls(
                     $exportMachine1,
                     $exportMachine2
                 );

        $instance->parse($dump, $exportData);
    }

    /**
     * @throws ReflectionException
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

        $instance = $this->createInstance();
        $result = $this->invokeMethod($instance, 'createMachine', $dumpMachine);

        $this->assertEquals($expectedResult, $result);
    }
}
