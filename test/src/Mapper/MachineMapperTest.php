<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Mapper;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Export\Entity\Dump\Machine as DumpMachine;
use FactorioItemBrowser\Export\Mapper\MachineMapper;
use FactorioItemBrowser\ExportData\Entity\Machine as ExportMachine;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the MachineMapper class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Mapper\MachineMapper
 */
class MachineMapperTest extends TestCase
{
    use ReflectionTrait;

    /**
     * @covers ::getSupportedDestinationClass
     * @covers ::getSupportedSourceClass
     */
    public function testGetSupportedClasses(): void
    {
        $mapper = new MachineMapper();

        $this->assertSame(DumpMachine::class, $mapper->getSupportedSourceClass());
        $this->assertSame(ExportMachine::class, $mapper->getSupportedDestinationClass());
    }

    /**
     * @covers ::map
     */
    public function testMap(): void
    {
        $source = new DumpMachine();
        $source->name = 'def';
        $source->localisedName = 'ghi';
        $source->localisedDescription = 'jkl';
        $source->craftingCategories = ['mno', 'pqr'];
        $source->craftingSpeed = 13.37;
        $source->itemSlots = 12;
        $source->fluidInputSlots = 34;
        $source->fluidOutputSlots = 56;
        $source->moduleSlots = 78;
        $source->energyUsage = 73.31;

        $expectedDestination = new ExportMachine();
        $expectedDestination->name = 'def';
        $expectedDestination->craftingCategories = ['mno', 'pqr'];
        $expectedDestination->craftingSpeed = 13.37;
        $expectedDestination->numberOfItemSlots = 12;
        $expectedDestination->numberOfFluidInputSlots = 34;
        $expectedDestination->numberOfFluidOutputSlots = 56;
        $expectedDestination->numberOfModuleSlots = 78;
        $expectedDestination->energyUsage = 73.31;
        $expectedDestination->energyUsageUnit = 'W';

        $destination = new ExportMachine();

        $mapper = new MachineMapper();
        $mapper->map($source, $destination);

        $this->assertEquals($expectedDestination, $destination);
    }

    /**
     * @return array<mixed>
     */
    public function provideConvertEnergyUsage(): array
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
     * @param float $energyUsage
     * @param float $expectedUsage
     * @param string $expectedUnit
     * @throws ReflectionException
     * @covers ::convertEnergyUsage
     * @dataProvider provideConvertEnergyUsage
     */
    public function testConvertEnergyUsage(float $energyUsage, float $expectedUsage, string $expectedUnit): void
    {
        $mapper = new MachineMapper();
        [$usage, $unit] = $this->invokeMethod($mapper, 'convertEnergyUsage', $energyUsage);

        $this->assertSame($expectedUsage, $usage);
        $this->assertSame($expectedUnit, $unit);
    }
}
