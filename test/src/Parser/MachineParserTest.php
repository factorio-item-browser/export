<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Parser;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Common\Constant\EntityType;
use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\Entity\Dump\Machine as DumpMachine;
use FactorioItemBrowser\Export\Parser\IconParser;
use FactorioItemBrowser\Export\Parser\MachineParser;
use FactorioItemBrowser\Export\Parser\TranslationParser;
use FactorioItemBrowser\ExportData\Entity\Combination;
use FactorioItemBrowser\ExportData\Entity\LocalisedString;
use FactorioItemBrowser\ExportData\Entity\Machine as ExportMachine;
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
        $parser = new MachineParser($this->iconParser, $this->translationParser);

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

        $parser = new MachineParser($this->iconParser, $this->translationParser);
        $parser->prepare($dump);

        $this->addToAssertionCount(1);
    }

    /**
     * Tests the parse method.
     * @covers ::parse
     */
    public function testParse(): void
    {
        /* @var DumpMachine&MockObject $dumpMachine1 */
        $dumpMachine1 = $this->createMock(DumpMachine::class);
        /* @var DumpMachine&MockObject $dumpMachine2 */
        $dumpMachine2 = $this->createMock(DumpMachine::class);
        /* @var ExportMachine&MockObject $exportMachine1 */
        $exportMachine1 = $this->createMock(ExportMachine::class);
        /* @var ExportMachine&MockObject $exportMachine2 */
        $exportMachine2 = $this->createMock(ExportMachine::class);

        $dump = new Dump();
        $dump->getControlStage()->setMachines([$dumpMachine1, $dumpMachine2]);

        /* @var Combination&MockObject $combination */
        $combination = $this->createMock(Combination::class);
        $combination->expects($this->exactly(2))
                    ->method('addMachine')
                    ->withConsecutive(
                        [$this->identicalTo($exportMachine1)],
                        [$this->identicalTo($exportMachine2)]
                    );

        /* @var MachineParser&MockObject $parser */
        $parser = $this->getMockBuilder(MachineParser::class)
                       ->onlyMethods(['mapMachine'])
                       ->setConstructorArgs([$this->iconParser, $this->translationParser])
                       ->getMock();
        $parser->expects($this->exactly(2))
               ->method('mapMachine')
               ->withConsecutive(
                   [$this->identicalTo($dumpMachine1)],
                   [$this->identicalTo($dumpMachine2)]
               )
               ->willReturnOnConsecutiveCalls(
                   $exportMachine1,
                   $exportMachine2
               );

        $parser->parse($dump, $combination);
    }

    /**
     * Tests the mapMachine method.
     * @throws ReflectionException
     * @covers ::mapMachine
     */
    public function testMapMachine(): void
    {
        $iconId = 'abc';

        $dumpMachine = new DumpMachine();
        $dumpMachine->setName('Def')
                    ->setCraftingCategories(['ghi', 'jkl'])
                    ->setCraftingSpeed(13.37)
                    ->setItemSlots(12)
                    ->setFluidInputSlots(34)
                    ->setFluidOutputSlots(56)
                    ->setModuleSlots(78)
                    ->setEnergyUsage(73.31);

        $expectedResult = new ExportMachine();
        $expectedResult->setName('def')
                       ->setCraftingCategories(['ghi', 'jkl'])
                       ->setCraftingSpeed(13.37)
                       ->setNumberOfItemSlots(12)
                       ->setNumberOfFluidInputSlots(34)
                       ->setNumberOfFluidOutputSlots(56)
                       ->setNumberOfModuleSlots(78)
                       ->setIconId($iconId);

        $this->iconParser->expects($this->once())
                         ->method('getIconId')
                         ->with(
                             $this->identicalTo(EntityType::MACHINE),
                             $this->identicalTo('def')
                         )
                         ->willReturn($iconId);

        $this->translationParser->expects($this->exactly(2))
                                ->method('translate')
                                ->withConsecutive(
                                    [
                                        $this->isInstanceOf(LocalisedString::class),
                                        $this->identicalTo($dumpMachine->getLocalisedName()),
                                        $this->isNull(),
                                    ],
                                    [
                                        $this->isInstanceOf(LocalisedString::class),
                                        $this->identicalTo($dumpMachine->getLocalisedDescription()),
                                        $this->isNull(),
                                    ],
                                );

        /* @var MachineParser&MockObject $parser */
        $parser = $this->getMockBuilder(MachineParser::class)
                       ->onlyMethods(['mapEnergyUsage'])
                       ->setConstructorArgs([$this->iconParser, $this->translationParser])
                       ->getMock();
        $parser->expects($this->once())
               ->method('mapEnergyUsage')
               ->with($this->equalTo($expectedResult));

        $result = $this->invokeMethod($parser, 'mapMachine', $dumpMachine);

        $this->assertEquals($expectedResult, $result);
    }


    /**
     * Provides the data for the mapEnergyUsage test.
     * @return array<mixed>
     */
    public function provideMapEnergyUsage(): array
    {
        return [
            [0., 0., 'W'],
            [42., 42., 'W'],
            [1000., 1., 'kW'],
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
     * Tests the mapEnergyUsage method.
     * @param float $energyUsage
     * @param float $expectedEnergyUsage
     * @param string $expectedUnit
     * @throws ReflectionException
     * @covers ::mapEnergyUsage
     * @dataProvider provideMapEnergyUsage
     */
    public function testMapEnergyUsage(float $energyUsage, float $expectedEnergyUsage, string $expectedUnit): void
    {
        /* @var ExportMachine&MockObject $exportMachine */
        $exportMachine = $this->createMock(ExportMachine::class);
        $exportMachine->expects($this->once())
                      ->method('setEnergyUsage')
                      ->with($this->identicalTo($expectedEnergyUsage))
                      ->willReturnSelf();
        $exportMachine->expects($this->once())
                      ->method('setEnergyUsageUnit')
                      ->with($this->identicalTo($expectedUnit))
                      ->willReturnSelf();

        $parser = new MachineParser($this->iconParser, $this->translationParser);
        $this->invokeMethod($parser, 'mapEnergyUsage', $exportMachine, $energyUsage);
    }

    /**
     * Tests the validate method.
     * @covers ::validate
     */
    public function testValidate(): void
    {
        /* @var Combination&MockObject $combination */
        $combination = $this->createMock(Combination::class);

        $parser = new MachineParser($this->iconParser, $this->translationParser);
        $parser->validate($combination);

        $this->addToAssertionCount(1);
    }
}
