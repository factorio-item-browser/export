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
use FactorioItemBrowser\ExportData\Collection\TranslationDictionary;
use FactorioItemBrowser\ExportData\ExportData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

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

    /**
     * @param array<string> $mockedMethods
     * @return TranslationParser&MockObject
     */
    private function createInstance(array $mockedMethods = []): TranslationParser
    {
        return $this->getMockBuilder(TranslationParser::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($mockedMethods)
                    ->setConstructorArgs([
                        $this->console,
                        $this->modFileManager,
                        $this->translator,
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

        $instance = $this->createInstance(['filterDuplicates']);
        $instance->expects($this->once())
                 ->method('filterDuplicates')
                 ->with($this->identicalTo($translations));

        $instance->translate($translations, $localisedString, $fallbackLocalisedString);
    }

    /**
     * @return array<mixed>
     */
    public function provideFilterDuplicates(): array
    {
        // Translations with duplication
        $translations1 = new TranslationDictionary();
        $translations1->set('en', 'abc');
        $translations1->set('de', 'abc');
        $translations1->set('fr', 'def');
        $translations1->set('ja', 'def');
        $expectedTranslations1 = new TranslationDictionary();
        $expectedTranslations1->set('en', 'abc');
        $expectedTranslations1->set('fr', 'def');
        $expectedTranslations1->set('ja', 'def');

        // Translations without duplication
        $translations2 = new TranslationDictionary();
        $translations2->set('en', 'abc');
        $translations2->set('de', 'def');
        $translations2->set('fr', 'ghi');
        $expectedTranslations2 = new TranslationDictionary();
        $expectedTranslations2->set('en', 'abc');
        $expectedTranslations2->set('de', 'def');
        $expectedTranslations2->set('fr', 'ghi');

        // Translations without English will never filter
        $translations3 = new TranslationDictionary();
        $translations3->set('de', 'abc');
        $translations3->set('fr', 'abc');
        $translations3->set('ja', 'ghi');
        $expectedTranslations3 = new TranslationDictionary();
        $expectedTranslations3->set('de', 'abc');
        $expectedTranslations3->set('fr', 'abc');
        $expectedTranslations3->set('ja', 'ghi');

        return [
            [$translations1, $expectedTranslations1],
        ];
    }

    /**
     * @throws ReflectionException
     * @dataProvider provideFilterDuplicates
     */
    public function testFilterDuplicates(
        DictionaryInterface $translations,
        DictionaryInterface $expectedTranslations
    ): void {
        $instance = $this->createInstance();
        $this->invokeMethod($instance, 'filterDuplicates', $translations);

        $this->assertEquals($expectedTranslations, $translations);
    }
}
