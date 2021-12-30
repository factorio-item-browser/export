<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\DataProcessor;

use BluePsyduck\FactorioModPortalClient\Entity\Version;
use FactorioItemBrowser\Export\DataProcessor\ModInfoAdder;
use FactorioItemBrowser\Export\Entity\InfoJson;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Output\Console;
use FactorioItemBrowser\Export\Service\ModFileService;
use FactorioItemBrowser\ExportData\Collection\ChunkedCollection;
use FactorioItemBrowser\ExportData\Entity\Mod;
use FactorioItemBrowser\ExportData\ExportData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the ModInfoAdder class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Export\DataProcessor\ModInfoAdder
 */
class ModInfoAdderTest extends TestCase
{
    /** @var Console&MockObject */
    private Console $console;
    /** @var ModFileService&MockObject */
    private ModFileService $modFileService;

    protected function setUp(): void
    {
        $this->console = $this->createMock(Console::class);
        $this->modFileService = $this->createMock(ModFileService::class);
    }

    private function createInstance(): ModInfoAdder
    {
        return new ModInfoAdder(
            $this->console,
            $this->modFileService
        );
    }

    /**
     * @throws ExportException
     */
    public function testProcess(): void
    {
        $mod1 = new Mod();
        $mod1->name = 'abc';

        $info1 = new InfoJson();
        $info1->version = new Version('1.2.3');
        $info1->author = 'def';
        $info1->title = 'ghi';
        $info1->description = 'jkl';

        $expectedMod1 = new Mod();
        $expectedMod1->name = 'abc';
        $expectedMod1->version = '1.2.3';
        $expectedMod1->author = 'def';
        $expectedMod1->localisedName = ['mod-name.abc'];
        $expectedMod1->localisedDescription = ['mod-description.abc'];
        $expectedMod1->labels->set('en', 'ghi');
        $expectedMod1->descriptions->set('en', 'jkl');

        $mod2 = new Mod();
        $mod2->name = 'mno';

        $info2 = new InfoJson();
        $info2->version = new Version('4.5.6');
        $info2->author = 'pqr';
        $info2->title = 'stu';
        $info2->description = 'vwx';

        $expectedMod2 = new Mod();
        $expectedMod2->name = 'mno';
        $expectedMod2->version = '4.5.6';
        $expectedMod2->author = 'pqr';
        $expectedMod2->localisedName = ['mod-name.mno'];
        $expectedMod2->localisedDescription = ['mod-description.mno'];
        $expectedMod2->labels->set('en', 'stu');
        $expectedMod2->descriptions->set('en', 'vwx');

        $mods = $this->createMock(ChunkedCollection::class);

        $exportData = $this->createMock(ExportData::class);
        $exportData->expects($this->any())
                   ->method('getMods')
                   ->willReturn($mods);

        $this->console->expects($this->once())
                      ->method('iterateWithProgressbar')
                      ->with($this->isType('string'), $this->identicalTo($mods))
                      ->willReturnCallback(fn() => yield from [$mod1, $mod2]);

        $this->modFileService->expects($this->any())
                             ->method('getInfo')
                             ->willReturnMap([
                                 ['abc', $info1],
                                 ['mno', $info2],
                             ]);

        $instance = $this->createInstance();
        $instance->process($exportData);

        $this->assertEquals($expectedMod1, $mod1);
        $this->assertEquals($expectedMod2, $mod2);
    }
}
