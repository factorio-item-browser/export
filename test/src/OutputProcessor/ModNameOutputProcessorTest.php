<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\OutputProcessor;

use FactorioItemBrowser\Export\Exception\DumpModNotLoadedException;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\OutputProcessor\ModNameOutputProcessor;
use FactorioItemBrowser\ExportData\Entity\Mod;
use FactorioItemBrowser\ExportData\ExportData;
use FactorioItemBrowser\ExportData\Storage\Storage;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the ModNameOutputProcessor class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Export\OutputProcessor\ModNameOutputProcessor
 */
class ModNameOutputProcessorTest extends TestCase
{
    /**
     * @return array<mixed>
     */
    public function provideProcessLine(): array
    {
        $mod1 = new Mod();
        $mod1->name = 'abc';
        $mod2 = new Mod();
        $mod2->name = 'def';
        $dumpMod = new Mod();
        $dumpMod->name = 'Dump';


        return [
            ['  69.420 Checksum of abc: 1337', [$mod2], [$mod2, $mod1]],
            ['   0.061 Loading mod base 0.18.21 (data.lua)', [$mod2], [$mod2]],
            ['1103.226 Checksum of Dump: 1832971175', [$mod1], [$mod1, $dumpMod]],
        ];
    }

    /**
     * @param string $outputLine
     * @param array<Mod> $mods
     * @param array<Mod> $expectedMods
     * @throws ExportException
     * @dataProvider provideProcessLine
     */
    public function testProcessLine(string $outputLine, array $mods, array $expectedMods): void
    {
        $exportData = new ExportData($this->createMock(Storage::class), 'test');
        foreach ($mods as $mod) {
            $exportData->getMods()->add($mod);
        }

        $instance = new ModNameOutputProcessor();
        $instance->processLine($outputLine, $exportData);

        $this->assertEquals($expectedMods, iterator_to_array($exportData->getMods()));
    }

    /**
     * @throws ExportException
     */
    public function testProcessExitCode(): void
    {
        $mod1 = new Mod();
        $mod1->name = 'abc';
        $mod2 = new Mod();
        $mod2->name = 'def';
        $dumpMod = new Mod();
        $dumpMod->name = 'Dump';

        $exportData = new ExportData($this->createMock(Storage::class), 'test');
        $exportData->getMods()->add($mod1)
                              ->add($mod2)
                              ->add($dumpMod);

        $instance = new ModNameOutputProcessor();
        $instance->processExitCode(0, $exportData);

        $this->addToAssertionCount(1);
    }

    /**
     * @throws ExportException
     */
    public function testProcessExitCodeWithException(): void
    {
        $mod1 = new Mod();
        $mod1->name = 'abc';
        $mod2 = new Mod();
        $mod2->name = 'def';

        $exportData = new ExportData($this->createMock(Storage::class), 'test');
        $exportData->getMods()->add($mod1)
                              ->add($mod2);

        $this->expectException(DumpModNotLoadedException::class);

        $instance = new ModNameOutputProcessor();
        $instance->processExitCode(0, $exportData);
    }
}
