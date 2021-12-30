<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\DataProcessor;

use BluePsyduck\FactorioTranslator\Exception\NoSupportedLoaderException;
use BluePsyduck\FactorioTranslator\Translator;
use FactorioItemBrowser\Export\DataProcessor\TranslationLoader;
use FactorioItemBrowser\Export\Output\Console;
use FactorioItemBrowser\Export\Service\ModFileService;
use FactorioItemBrowser\ExportData\Collection\ChunkedCollection;
use FactorioItemBrowser\ExportData\Entity\Mod;
use FactorioItemBrowser\ExportData\ExportData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the TranslationLoader class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Export\DataProcessor\TranslationLoader
 */
class TranslationLoaderTest extends TestCase
{
    /** @var Console&MockObject */
    private Console $console;
    /** @var ModFileService&MockObject */
    private ModFileService $modFileService;
    /** @var Translator&MockObject */
    private Translator $translator;

    protected function setUp(): void
    {
        $this->console = $this->createMock(Console::class);
        $this->modFileService = $this->createMock(ModFileService::class);
        $this->translator = $this->createMock(Translator::class);
    }

    private function createInstance(): TranslationLoader
    {
        return new TranslationLoader(
            $this->console,
            $this->modFileService,
            $this->translator,
        );
    }

    /**
     * @throws NoSupportedLoaderException
     */
    public function testProcess(): void
    {
        $mod1 = new Mod();
        $mod1->name = 'abc';
        $mod2 = new Mod();
        $mod2->name = 'def';

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
                             ->method('getLocalDirectory')
                             ->willReturnMap([
                                 ['core', 'eroc'],
                                 ['abc', 'cba'],
                                 ['def', 'fed'],
                             ]);

        $this->translator->expects($this->exactly(3))
                         ->method('loadMod')
                         ->withConsecutive(
                             [$this->identicalTo('eroc')],
                             [$this->identicalTo('cba')],
                             [$this->identicalTo('fed')],
                         );

        $instance = $this->createInstance();
        $instance->process($exportData);
    }
}
