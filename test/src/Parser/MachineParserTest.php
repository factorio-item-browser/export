<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Parser;

use BluePsyduck\Common\Data\DataContainer;
use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Export\I18n\Translator;
use FactorioItemBrowser\Export\Parser\IconParser;
use FactorioItemBrowser\Export\Parser\MachineParser;
use FactorioItemBrowser\ExportData\Entity\LocalisedString;
use FactorioItemBrowser\ExportData\Entity\Machine;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;
use FactorioItemBrowser\ExportData\Registry\EntityRegistry;
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

    /**
     * Tests the constructing.
     * @covers ::__construct
     * @throws ReflectionException
     */
    public function testConstruct(): void
    {
        /* @var IconParser $iconParser */
        $iconParser = $this->createMock(IconParser::class);
        /* @var EntityRegistry $machineRegistry */
        $machineRegistry = $this->createMock(EntityRegistry::class);
        /* @var Translator $translator */
        $translator = $this->createMock(Translator::class);

        $parser = new MachineParser($iconParser, $machineRegistry, $translator);
        $this->assertSame($iconParser, $this->extractProperty($parser, 'iconParser'));
        $this->assertSame($machineRegistry, $this->extractProperty($parser, 'machineRegistry'));
        $this->assertSame($translator, $this->extractProperty($parser, 'translator'));
    }

    /**
     * Tests the parse method.
     * @covers ::parse
     */
    public function testParse(): void
    {
        $dumpData = new DataContainer(['abc' => 'def']);
        $machine1 = (new Machine())->setName('ghi');
        $machine2 = (new Machine())->setName('jkl');
        $machineHash1 = 'mno';
        $machineHash2 = 'pqr';
        $machines = [$machine1, $machine2];

        /* @var EntityRegistry|MockObject $machineRegistry */
        $machineRegistry = $this->getMockBuilder(EntityRegistry::class)
                                ->setMethods(['set'])
                                ->disableOriginalConstructor()
                                ->getMock();
        $machineRegistry->expects($this->exactly(2))
                        ->method('set')
                        ->withConsecutive(
                            [$machine1],
                            [$machine2]
                        )
                        ->willReturnOnConsecutiveCalls(
                            $machineHash1,
                            $machineHash2
                        );

        /* @var Combination|MockObject $combination */
        $combination = $this->getMockBuilder(Combination::class)
                            ->setMethods(['addMachineHash'])
                            ->disableOriginalConstructor()
                            ->getMock();
        $combination->expects($this->exactly(2))
                    ->method('addMachineHash')
                    ->withConsecutive(
                        [$machineHash1],
                        [$machineHash2]
                    );

        /* @var IconParser $iconParser */
        $iconParser = $this->createMock(IconParser::class);
        /* @var Translator $translator */
        $translator = $this->createMock(Translator::class);

        /* @var MachineParser|MockObject $parser */
        $parser = $this->getMockBuilder(MachineParser::class)
                       ->setMethods(['parseMachines', 'parseFluidBoxes', 'assignIconHash'])
                       ->setConstructorArgs([$iconParser, $machineRegistry, $translator])
                       ->getMock();
        $parser->expects($this->once())
               ->method('parseMachines')
               ->with($dumpData)
               ->willReturn($machines);
        $parser->expects($this->once())
               ->method('parseFluidBoxes')
               ->with($dumpData, $machines);
        $parser->expects($this->exactly(2))
               ->method('assignIconHash')
               ->withConsecutive(
                   [$combination, $machine1],
                   [$combination, $machine2]
               );

        $parser->parse($combination, $dumpData);
    }

    /**
     * Tests the parseMachines method.
     * @covers ::parseMachines
     * @throws ReflectionException
     */
    public function testParseMachines(): void
    {
        $dumpData = new DataContainer([
            'machines' => [
                ['abc' => 'def'],
                ['ghi' => 'jkl'],
            ]
        ]);
        $machineData1 = new DataContainer(['abc' => 'def']);
        $machineData2 = new DataContainer(['ghi' => 'jkl']);
        $machine1 = (new Machine())->setName('mno');
        $machine2 = (new Machine())->setName('pqr');
        $expectedResult = [
            'mno' => $machine1,
            'pqr' => $machine2
        ];


        /* @var MachineParser|MockObject $parser */
        $parser = $this->getMockBuilder(MachineParser::class)
                       ->setMethods(['parseMachine', 'addTranslations'])
                       ->disableOriginalConstructor()
                       ->getMock();
        $parser->expects($this->exactly(2))
               ->method('parseMachine')
               ->withConsecutive(
                   [$this->equalTo($machineData1)],
                   [$this->equalTo($machineData2)]
               )
               ->willReturnOnConsecutiveCalls(
                   $machine1,
                   $machine2
               );
        $parser->expects($this->exactly(2))
               ->method('addTranslations')
               ->withConsecutive(
                   [$machine1, $this->equalTo($machineData1)],
                   [$machine2, $this->equalTo($machineData2)]
               );

        $result = $this->invokeMethod($parser, 'parseMachines', $dumpData);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the parseMachine method.
     * @covers ::parseMachine
     * @throws ReflectionException
     */
    public function testParseMachine(): void
    {
        $machineData = new DataContainer([
            'name' => 'Abc',
            'craftingSpeed' => 4.2,
            'numberOfItemSlots' => 42,
            'numberOfModuleSlots' => 21,
            'craftingCategories' => [
                'def' => true,
                'ghi' => false,
                'mno' => true,
                '' => true,
            ],
        ]);
        $expectedResult = new Machine();
        $expectedResult->setName('abc')
                       ->setCraftingSpeed(4.2)
                       ->setNumberOfItemSlots(42)
                       ->setNumberOfModuleSlots(21)
                       ->setCraftingCategories(['def', 'mno']);

        /* @var MachineParser|MockObject $parser */
        $parser = $this->getMockBuilder(MachineParser::class)
                       ->setMethods(['parseEnergyUsage'])
                       ->disableOriginalConstructor()
                       ->getMock();
        $parser->expects($this->once())
               ->method('parseEnergyUsage')
               ->with($this->equalTo($expectedResult), $machineData);

        $result = $this->invokeMethod($parser, 'parseMachine', $machineData);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Provides the data for the parseEnergyUsage test.
     * @return array
     */
    public function provideParseEnergyUsage(): array
    {
        return [
            [0., null, null],
            [42., 42., 'W'],
            [1337., 1.337, 'kW'],
            [1337000., 1.337, 'MW'],
            [1337000000., 1.337, 'GW'],
            [1337000000000., 1.337, 'TW'],
            [1337000000000000., 1.337, 'PW'],
            [1337000000000000000., 1.337, 'EW'],
            [1337000000000000000000., 1.337, 'ZW'],
            [1337000000000000000000000., 1.337, 'YW'],
        ];
    }

    /**
     * Tests the parseEnergyUsage method.
     * @param float $energyUsage
     * @param float|null $expectedEnergyUsage
     * @param null|string $expectedEnergyUsageUnit
     * @throws ReflectionException
     * @covers ::parseEnergyUsage
     * @dataProvider provideParseEnergyUsage
     */
    public function testParseEnergyUsage(
        float $energyUsage,
        ?float $expectedEnergyUsage,
        ?string $expectedEnergyUsageUnit
    ): void {
        $machineData = new DataContainer([
            'energyUsage' => $energyUsage,
        ]);

        /* @var Machine|MockObject $machine */
        $machine = $this->getMockBuilder(Machine::class)
                        ->setMethods(['setEnergyUsage', 'setEnergyUsageUnit'])
                        ->disableOriginalConstructor()
                        ->getMock();
        $machine->expects($expectedEnergyUsage === null ? $this->never() : $this->once())
                ->method('setEnergyUsage')
                ->with($expectedEnergyUsage)
                ->willReturnSelf();
        $machine->expects($expectedEnergyUsageUnit === null ? $this->never() : $this->once())
                ->method('setEnergyUsageUnit')
                ->with($expectedEnergyUsageUnit)
                ->willReturnSelf();

        /* @var IconParser $iconParser */
        $iconParser = $this->createMock(IconParser::class);
        /* @var EntityRegistry $machineRegistry */
        $machineRegistry = $this->createMock(EntityRegistry::class);
        /* @var Translator $translator */
        $translator = $this->createMock(Translator::class);

        $parser = new MachineParser($iconParser, $machineRegistry, $translator);
        $this->invokeMethod($parser, 'parseEnergyUsage', $machine, $machineData);
    }

    /**
     * Tests the parseFluidBoxes method.
     * @covers ::parseFluidBoxes
     * @throws ReflectionException
     */
    public function testParseFluidBoxes(): void
    {
        $dumpData = new DataContainer([
            'fluidBoxes' => [
                'abc' => ['name' => 'Abc'],
                'def' => ['name' => 'def'],
            ],
        ]);

        $machine1 = (new Machine())->setName('abc');
        $machine2 = (new Machine())->setName('ghi');
        $machines = [
            'abc' => $machine1,
            'ghi' => $machine2
        ];

        /* @var MachineParser|MockObject $parser */
        $parser = $this->getMockBuilder(MachineParser::class)
                       ->setMethods(['parseFluidBox'])
                       ->disableOriginalConstructor()
                       ->getMock();
        $parser->expects($this->once())
               ->method('parseFluidBox')
               ->with($machine1, $this->equalTo(new DataContainer(['name' => 'Abc'])));

        $this->invokeMethod($parser, 'parseFluidBoxes', $dumpData, $machines);
    }

    /**
     * Tests the parseFluidBox method.
     * @covers ::parseFluidBox
     * @throws ReflectionException
     */
    public function testParseFluidBox(): void
    {
        $fluidBoxData = new DataContainer([
            'input' => 42,
            'output' => 21,
        ]);

        /* @var Machine|MockObject $machine */
        $machine = $this->getMockBuilder(Machine::class)
                        ->setMethods(['setNumberOfFluidInputSlots', 'setNumberOfFluidOutputSlots'])
                        ->disableOriginalConstructor()
                        ->getMock();
        $machine->expects($this->once())
                ->method('setNumberOfFluidInputSlots')
                ->with(42)
                ->willReturnSelf();
        $machine->expects($this->once())
                ->method('setNumberOfFluidOutputSlots')
                ->with(21)
                ->willReturnSelf();

        /* @var IconParser $iconParser */
        $iconParser = $this->createMock(IconParser::class);
        /* @var EntityRegistry $machineRegistry */
        $machineRegistry = $this->createMock(EntityRegistry::class);
        /* @var Translator $translator */
        $translator = $this->createMock(Translator::class);

        $parser = new MachineParser($iconParser, $machineRegistry, $translator);
        $this->invokeMethod($parser, 'parseFluidBox', $machine, $fluidBoxData);
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

        $machineData = new DataContainer([
            'localised' => [
                'name' => ['ghi'],
                'description' => ['jkl'],
            ]
        ]);

        /* @var Machine|MockObject $machine */
        $machine = $this->getMockBuilder(Machine::class)
                        ->setMethods(['getLabels', 'getDescriptions'])
                        ->disableOriginalConstructor()
                        ->getMock();
        $machine->expects($this->once())
                ->method('getLabels')
                ->willReturn($labels);
        $machine->expects($this->once())
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
                       [$labels, 'name', ['ghi'], null],
                       [$descriptions, 'description', ['jkl'], null]
                   );

        /* @var IconParser $iconParser */
        $iconParser = $this->createMock(IconParser::class);
        /* @var EntityRegistry $machineRegistry */
        $machineRegistry = $this->createMock(EntityRegistry::class);

        $parser = new MachineParser($iconParser, $machineRegistry, $translator);
        $this->invokeMethod($parser, 'addTranslations', $machine, $machineData);
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
        $name = 'abc';
        $combination = new Combination();

        /* @var IconParser|MockObject $iconParser */
        $iconParser = $this->getMockBuilder(IconParser::class)
                           ->setMethods(['getIconHashForEntity'])
                           ->disableOriginalConstructor()
                           ->getMock();
        $iconParser->expects($this->once())
                   ->method('getIconHashForEntity')
                   ->with($combination, 'machine', $name)
                   ->willReturn($resultHash);

        /* @var Machine|MockObject $machine */
        $machine = $this->getMockBuilder(Machine::class)
                        ->setMethods(['getName', 'setIconHash'])
                        ->disableOriginalConstructor()
                        ->getMock();
        $machine->expects($this->once())
                ->method('getName')
                ->willReturn($name);
        $machine->expects($expectSet ? $this->once() : $this->never())
                ->method('setIconHash')
                ->with((string) $resultHash);

        /* @var EntityRegistry $machineRegistry */
        $machineRegistry = $this->createMock(EntityRegistry::class);
        /* @var Translator $translator */
        $translator = $this->createMock(Translator::class);

        $parser = new MachineParser($iconParser, $machineRegistry, $translator);
        $this->invokeMethod($parser, 'assignIconHash', $combination, $machine);
    }
}
