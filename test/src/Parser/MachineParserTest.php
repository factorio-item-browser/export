<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Parser;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Common\Constant\EntityType;
use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\Entity\Dump\Machine as DumpMachine;
use FactorioItemBrowser\Export\Exception\ExportException;
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

    /** @var IconParser&MockObject */
    private IconParser $iconParser;
    /** @var TranslationParser&MockObject */
    private TranslationParser $translationParser;

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
        $parser = new MachineParser($this->iconParser, $this->translationParser);

        $this->assertSame($this->iconParser, $this->extractProperty($parser, 'iconParser'));
        $this->assertSame($this->translationParser, $this->extractProperty($parser, 'translationParser'));
    }

    /**
     * Tests the prepare method.
     * @throws ExportException
     * @covers ::prepare
     */
    public function testPrepare(): void
    {
        $dump = $this->createMock(Dump::class);

        $parser = new MachineParser($this->iconParser, $this->translationParser);
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
        $dumpMachine1 = $this->createMock(DumpMachine::class);
        $dumpMachine2 = $this->createMock(DumpMachine::class);
        $exportMachine1 = $this->createMock(ExportMachine::class);
        $exportMachine2 = $this->createMock(ExportMachine::class);

        $dump = new Dump();
        $dump->machines = [$dumpMachine1, $dumpMachine2];

        $combination = $this->createMock(Combination::class);
        $combination->expects($this->exactly(2))
                    ->method('addMachine')
                    ->withConsecutive(
                        [$this->identicalTo($exportMachine1)],
                        [$this->identicalTo($exportMachine2)]
                    );

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
        $dumpMachine->name = 'def';
        $dumpMachine->localisedName = 'ghi';
        $dumpMachine->localisedDescription = 'jkl';
        $dumpMachine->craftingCategories = ['mno', 'pqr'];
        $dumpMachine->craftingSpeed = 13.37;
        $dumpMachine->itemSlots = 12;
        $dumpMachine->fluidInputSlots = 34;
        $dumpMachine->fluidOutputSlots = 56;
        $dumpMachine->moduleSlots = 78;
        $dumpMachine->energyUsage = 73.31;

        $expectedResult = new ExportMachine();
        $expectedResult->setName('def')
                       ->setCraftingCategories(['mno', 'pqr'])
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
                                        $this->identicalTo('ghi'),
                                        $this->isNull(),
                                    ],
                                    [
                                        $this->isInstanceOf(LocalisedString::class),
                                        $this->identicalTo('jkl'),
                                        $this->isNull(),
                                    ],
                                );

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
     * @throws ExportException
     * @covers ::validate
     */
    public function testValidate(): void
    {
        $combination = $this->createMock(Combination::class);

        $parser = new MachineParser($this->iconParser, $this->translationParser);
        $parser->validate($combination);

        $this->addToAssertionCount(1);
    }
}
