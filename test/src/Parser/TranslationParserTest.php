<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Parser;

use BluePsyduck\FactorioTranslator\Exception\NoSupportedLoaderException;
use BluePsyduck\FactorioTranslator\Translator;
use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Output\Console;
use FactorioItemBrowser\Export\Service\ModFileService;
use FactorioItemBrowser\Export\Parser\TranslationParser;
use FactorioItemBrowser\ExportData\Collection\DictionaryInterface;
use FactorioItemBrowser\ExportData\ExportData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the TranslationParser class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Export\Parser\TranslationParser
 */
class TranslationParserTest extends TestCase
{
    use ReflectionTrait;

    /** @var Console&MockObject */
    private Console $console;
    /** @var ModFileService&MockObject */
    private ModFileService $modFileManager;
    /** @var Translator&MockObject */
    private Translator $translator;

    protected function setUp(): void
    {
        $this->console = $this->createMock(Console::class);
        $this->modFileManager = $this->createMock(ModFileService::class);
        $this->translator = $this->createMock(Translator::class);
    }

    private function createInstance(): TranslationParser
    {
        return new TranslationParser(
            $this->console,
            $this->modFileManager,
            $this->translator,
        );
    }

    /**
     * @throws ExportException
     */
    public function testEmptyMethods(): void
    {
        $dump = $this->createMock(Dump::class);
        $exportData = $this->createMock(ExportData::class);

        $instance = $this->createInstance();
        $instance->parse($dump, $exportData);
        $instance->validate($exportData);

        $this->addToAssertionCount(1);
    }

    /**
     * @throws NoSupportedLoaderException
     */
    public function testPrepare(): void
    {
        $dump = new Dump();
        $dump->modNames = ['abc', 'def'];

        $this->console->expects($this->once())
                      ->method('iterateWithProgressbar')
                      ->with($this->isType('string'), $this->identicalTo(['abc', 'def']))
                      ->willReturnCallback(fn() => yield from ['cba', 'fed']);

        $this->modFileManager->expects($this->exactly(3))
                             ->method('getLocalDirectory')
                             ->withConsecutive(
                                 [$this->identicalTo('core')],
                                 [$this->identicalTo('cba')],
                                 [$this->identicalTo('fed')],
                             )
                             ->willReturnOnConsecutiveCalls(
                                 'ghi',
                                 'jkl',
                                 'mno',
                             );

        $this->translator->expects($this->exactly(3))
                         ->method('loadMod')
                         ->withConsecutive(
                             [$this->identicalTo('ghi')],
                             [$this->identicalTo('jkl')],
                             [$this->identicalTo('mno')],
                         )
                         ->willReturnSelf();

        $instance = $this->createInstance();
        $instance->prepare($dump);
    }

    public function testTranslate(): void
    {
        $locales = ['abc', 'def', 'ghi'];
        $localisedString = 'foo';
        $fallbackLocalisedString = 'bar';

        $translations = $this->createMock(DictionaryInterface::class);
        $translations->expects($this->exactly(2))
                     ->method('set')
                     ->withConsecutive(
                         [$this->identicalTo('abc'), $this->identicalTo('jkl')],
                         [$this->identicalTo('def'), $this->identicalTo('mno')]
                     );

        $this->translator->expects($this->once())
                         ->method('getAllLocales')
                         ->willReturn($locales);
        $this->translator->expects($this->exactly(5))
                         ->method('translate')
                         ->withConsecutive(
                             [$this->identicalTo('abc'), $this->identicalTo($localisedString)],
                             [$this->identicalTo('def'), $this->identicalTo($localisedString)],
                             [$this->identicalTo('def'), $this->identicalTo($fallbackLocalisedString)],
                             [$this->identicalTo('ghi'), $this->identicalTo($localisedString)],
                             [$this->identicalTo('ghi'), $this->identicalTo($fallbackLocalisedString)],
                         )
                         ->willReturnOnConsecutiveCalls(
                             'jkl',
                             '',
                             'mno',
                             '',
                             '',
                         );

        $instance = $this->createInstance();
        $instance->translate($translations, $localisedString, $fallbackLocalisedString);
    }
}
