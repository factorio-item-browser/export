<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\I18n;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\I18n\Translator;
use FactorioItemBrowser\Export\Mod\LocaleReader;
use FactorioItemBrowser\ExportData\Entity\LocalisedString;
use FactorioItemBrowser\ExportData\Entity\Mod;
use FactorioItemBrowser\ExportData\Registry\ModRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Zend\I18n\Translator\Translator as ZendTranslator;

/**
 * The PHPUnit test of the Translator class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\I18n\Translator
 */
class TranslatorTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests the constructing.
     * @covers ::__construct
     * @throws ReflectionException
     */
    public function testConstruct(): void
    {
        /* @var LocaleReader $localeReader */
        $localeReader = $this->createMock(LocaleReader::class);
        /* @var ModRegistry $modRegistry */
        $modRegistry = $this->createMock(ModRegistry::class);
        /* @var ZendTranslator $placeholderTranslator */
        $placeholderTranslator = $this->createMock(ZendTranslator::class);

        $command = new Translator($localeReader, $modRegistry, $placeholderTranslator);
        $this->assertSame($localeReader, $this->extractProperty($command, 'localeReader'));
        $this->assertSame($modRegistry, $this->extractProperty($command, 'modRegistry'));
        $this->assertSame($placeholderTranslator, $this->extractProperty($command, 'placeholderTranslator'));
    }

    /**
     * Tests the loadFromModNames method.
     * @throws ExportException
     * @throws ReflectionException
     * @covers ::loadFromModNames
     */
    public function testLoadFromModNames(): void
    {
        $mod1 = (new Mod())->setName('abc');
        $mod2 = (new Mod())->setName('def');
        $modNames = ['abc', 'def', 'ghi'];

        $translations1 = [
            'jkl' => [
                'mno' => 'pqr',
            ],
            'stu' => [
                'vwx' => 'yza',
            ],
        ];
        $translations2 = [
            'jkl' => [
                'bcd' => 'efg',
            ],
            'stu' => [
                'vwx' => 'hij',
            ],
        ];
        $expectedTranslations = [
            'jkl' => [
                'mno' => 'pqr',
                'bcd' => 'efg',
            ],
            'stu' => [
                'vwx' => 'hij',
            ],
        ];

        /* @var ModRegistry|MockObject $modRegistry */
        $modRegistry = $this->getMockBuilder(ModRegistry::class)
                            ->setMethods(['get'])
                            ->disableOriginalConstructor()
                            ->getMock();
        $modRegistry->expects($this->exactly(3))
                    ->method('get')
                    ->withConsecutive(
                        ['abc'],
                        ['def'],
                        ['ghi']
                    )
                    ->willReturnOnConsecutiveCalls(
                        $mod1,
                        $mod2,
                        null
                    );

        /* @var LocaleReader|MockObject $localeReader */
        $localeReader = $this->getMockBuilder(LocaleReader::class)
                             ->setMethods(['read'])
                             ->disableOriginalConstructor()
                             ->getMock();
        $localeReader->expects($this->exactly(2))
                     ->method('read')
                     ->withConsecutive(
                         [$mod1],
                         [$mod2]
                     )
                     ->willReturnOnConsecutiveCalls(
                         $translations1,
                         $translations2
                     );

        /* @var ZendTranslator $placeholderTranslator */
        $placeholderTranslator = $this->createMock(ZendTranslator::class);

        $translator = new Translator($localeReader, $modRegistry, $placeholderTranslator);
        $this->injectProperty($translator, 'translations', ['foo' => 'bar']);

        $translator->loadFromModNames($modNames);
        $this->assertSame($expectedTranslations, $this->extractProperty($translator, 'translations'));
    }

    /**
     * Tests the addTranslationsToEntity method.
     * @throws ReflectionException
     * @covers ::addTranslationsToEntity
     */
    public function testAddTranslationsToEntity(): void
    {
        $type = 'abc';
        $localisedString = 'def';
        $fallbackLocalisedString = 'ghi';

        $translations = [
            'jkl' => 'lkj',
            'mno' => 'onm',
            'pqr' => 'rqp',
        ];

        /* @var LocalisedString|MockObject $entity */
        $entity = $this->getMockBuilder(LocalisedString::class)
                       ->setMethods(['setTranslation'])
                       ->disableOriginalConstructor()
                       ->getMock();
        $entity->expects($this->exactly(2))
               ->method('setTranslation')
               ->withConsecutive(
                   ['jkl', 'stu'],
                   ['pqr', 'vwx']
               );

        /* @var Translator|MockObject $translator */
        $translator = $this->getMockBuilder(Translator::class)
                           ->setMethods(['translateWithFallback'])
                           ->disableOriginalConstructor()
                           ->getMock();
        $translator->expects($this->exactly(3))
                   ->method('translateWithFallback')
                   ->withConsecutive(
                       ['jkl', $type, $localisedString, $fallbackLocalisedString],
                       ['mno', $type, $localisedString, $fallbackLocalisedString],
                       ['pqr', $type, $localisedString, $fallbackLocalisedString]
                   )
                   ->willReturnOnConsecutiveCalls(
                       'stu',
                       '',
                       'vwx'
                   );
        $this->injectProperty($translator, 'translations', $translations);

        $translator->addTranslationsToEntity($entity, $type, $localisedString, $fallbackLocalisedString);
    }

    /**
     * Provides the data for the translateWithFallback test.
     * @return array
     */
    public function provideTranslateWithFallback(): array
    {
        return [
            ['foo', ['bar'], 'bar'],
            ['foo', ['', 'bar'], 'bar'],
            [null, [''], ''],
        ];
    }

    /**
     * Tests the translateWithFallback method.
     * @param string|array $fallbackLocalisedString
     * @param array $resultsTranslate
     * @param string $expectedResult
     * @throws ReflectionException
     * @covers ::translateWithFallback
     * @dataProvider provideTranslateWithFallback
     */
    public function testTranslateWithFallback(
        $fallbackLocalisedString,
        array $resultsTranslate,
        string $expectedResult
    ): void {
        $locale = 'abc';
        $type = 'def';
        $localisedString = 'ghi';

        /* @var Translator|MockObject $translator */
        $translator = $this->getMockBuilder(Translator::class)
                           ->setMethods(['translate'])
                           ->disableOriginalConstructor()
                           ->getMock();
        $translator->expects($this->exactly(count($resultsTranslate)))
                   ->method('translate')
                   ->withConsecutive(
                       [$locale, $type, $localisedString, 1],
                       [$locale, $type, $fallbackLocalisedString, 1]
                   )
                   ->willReturnOnConsecutiveCalls(
                       ...$resultsTranslate
                   );

        $result = $this->invokeMethod(
            $translator,
            'translateWithFallback',
            $locale,
            $type,
            $localisedString,
            $fallbackLocalisedString
        );
        $this->assertSame($expectedResult, $result);
    }

    /**
     * Provides the data for the translate test.
     * @return array
     */
    public function provideTranslate(): array
    {
        return [
            ['abc', 42, ['def'], 'def'],
            ['abc', 42, ['', 'ghi'], 'ghi'],
            ['abc', 1, [''], ''],
            ['en', 42, [''], ''],
        ];
    }

    /**
     * Tests the translate method.
     * @param string $locale
     * @param int $level
     * @param array $resultsTranslate
     * @param string $paramResolveReferences
     * @throws ReflectionException
     * @covers ::translate
     * @dataProvider provideTranslate
     */
    public function testTranslate(
        string $locale,
        int $level,
        array $resultsTranslate,
        string $paramResolveReferences
    ): void {
        $type = 'foo';
        $localisedString = 'bar';
        $resultResolveReferences = 'zyx';

        /* @var Translator|MockObject $translator */
        $translator = $this->getMockBuilder(Translator::class)
                           ->setMethods(['translateLocalisedString', 'resolveReferences'])
                           ->disableOriginalConstructor()
                           ->getMock();
        $translator->expects($this->exactly(count($resultsTranslate)))
                   ->method('translateLocalisedString')
                   ->withConsecutive(
                       [$locale, $type, $localisedString, $level],
                       ['en', $type, $localisedString, $level]
                   )
                   ->willReturnOnConsecutiveCalls(
                       ...$resultsTranslate
                   );
        $translator->expects($this->once())
                   ->method('resolveReferences')
                   ->with($locale, $type, $paramResolveReferences)
                   ->willReturn($resultResolveReferences);

        $result = $this->invokeMethod($translator, 'translate', $locale, $type, $localisedString, $level);
        $this->assertSame($resultResolveReferences, $result);
    }

    /**
     * Provides the data for the translateLocalisedString test.
     * @return array
     */
    public function provideTranslateLocalisedString(): array
    {
        $translations = [
            'abc' => [
                'def' => 'ghi',
            ],
        ];

        return [
            [$translations, 'abc', 'jkl', null, null, null, 'jkl'],
            [$translations, 'abc', ['', 'jkl'], null, null, null, 'jkl'],
            [$translations, 'abc', ['def'], null, null, null, 'ghi'],
            [$translations, 'abc', ['jkl'], null, null, null, ''],
            [$translations, 'abc', ['def', 'jkl', 'mno'], 'pqr', 'ghi', ['jkl', 'mno'], 'pqr'],
        ];
    }

    /**
     * Tests the translateLocalisedString method.
     * @param array $translations
     * @param string $locale
     * @param string|array $localisedString
     * @param string|null $resultTranslateParameters
     * @param string|null $expectedString
     * @param array|null $expectedParameters
     * @param string $expectedResult
     * @throws ReflectionException
     * @covers ::translateLocalisedString
     * @dataProvider provideTranslateLocalisedString
     */
    public function testTranslateLocalisedString(
        array $translations,
        string $locale,
        $localisedString,
        ?string $resultTranslateParameters,
        ?string $expectedString,
        ?array $expectedParameters,
        string $expectedResult
    ): void {
        $type = 'foo';
        $level = 42;

        /* @var Translator|MockObject $translator */
        $translator = $this->getMockBuilder(Translator::class)
                           ->setMethods(['translateParameters'])
                           ->disableOriginalConstructor()
                           ->getMock();
        $translator->expects($resultTranslateParameters === null ? $this->never() : $this->once())
                   ->method('translateParameters')
                   ->with($locale, $type, $expectedString, $expectedParameters, $level)
                   ->willReturn($resultTranslateParameters);
        $this->injectProperty($translator, 'translations', $translations);

        $result = $this->invokeMethod(
            $translator,
            'translateLocalisedString',
            $locale,
            $type,
            $localisedString,
            $level
        );
        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the translateParameters method.
     * @throws ReflectionException
     * @covers ::translateParameters
     */
    public function testTranslateParameters(): void
    {
        $locale = 'abc';
        $type = 'def';
        $string = 'ghi __1__ jkl __2__ mno';
        $parameters = ['pqr', 'stu'];
        $level = 42;
        $expectedResult = 'ghi rqp jkl uts mno';

        /* @var Translator|MockObject $translator */
        $translator = $this->getMockBuilder(Translator::class)
                           ->setMethods(['translate'])
                           ->disableOriginalConstructor()
                           ->getMock();
        $translator->expects($this->exactly(2))
                   ->method('translate')
                   ->withConsecutive(
                       [$locale, $type, 'pqr', 43],
                       [$locale, $type, 'stu', 43]
                   )
                   ->willReturnOnConsecutiveCalls(
                       'rqp',
                       'uts'
                   );

        $result = $this->invokeMethod($translator, 'translateParameters', $locale, $type, $string, $parameters, $level);
        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the resolveReferences method.
     * @throws ReflectionException
     * @covers ::resolveReferences
     */
    public function testResolveReferences(): void
    {
        $locale = 'abc';
        $type = 'def';
        $string = 'ghi __jkl__mno__ pqr __stu__vwx__ yza';
        $expectedResult = 'ghi bcd pqr __stu__vwx__ yza';

        /* @var Translator|MockObject $translator */
        $translator = $this->getMockBuilder(Translator::class)
                           ->setMethods(['translateReference'])
                           ->disableOriginalConstructor()
                           ->getMock();
        $translator->expects($this->exactly(2))
                   ->method('translateReference')
                   ->withConsecutive(
                       [$locale, 'jkl', $type, 'mno'],
                       [$locale, 'stu', $type, 'vwx']
                   )
                   ->willReturnOnConsecutiveCalls(
                       'bcd',
                       null
                   );

        $result = $this->invokeMethod($translator, 'resolveReferences', $locale, $type, $string);
        $this->assertSame($expectedResult, $result);
    }

    /**
     * Provides the data for the translateReference test.
     * @return array
     */
    public function provideTranslateReference(): array
    {
        $translations = [
            'abc' => [
                'def-ghi.jkl' => 'mno'
            ]
        ];

        return [
            [$translations, 'abc', 'def', 'ghi', 'jkl', false, null, 'mno'],
            [$translations, 'abc', 'def', 'ghi', 'pqr', true, 'stu', 'stu'],
            [$translations, 'abc', 'def', 'ghi', 'pqr', true, null, null],
        ];
    }

    /**
     * Tests the translateReference method.
     * @param array $translations
     * @param string $locale
     * @param string $section
     * @param string $type
     * @param string $name
     * @param bool $expectPlaceholder
     * @param string|null $resultPlaceholder
     * @param string|null $expectedResult
     * @throws ReflectionException
     * @covers ::translateReference
     * @dataProvider provideTranslateReference
     */
    public function testTranslateReference(
        array $translations,
        string $locale,
        string $section,
        string $type,
        string $name,
        bool $expectPlaceholder,
        ?string $resultPlaceholder,
        ?string $expectedResult
    ): void {
        /* @var Translator|MockObject $translator */
        $translator = $this->getMockBuilder(Translator::class)
                           ->setMethods(['translatePlaceholder'])
                           ->disableOriginalConstructor()
                           ->getMock();
        $translator->expects($expectPlaceholder ? $this->once() : $this->never())
                   ->method('translatePlaceholder')
                   ->with($locale, $section, $name)
                   ->willReturn($resultPlaceholder);
        $this->injectProperty($translator, 'translations', $translations);

        $result = $this->invokeMethod($translator, 'translateReference', $locale, $section, $type, $name);
        $this->assertSame($expectedResult, $result);
    }

    /**
     * Provides the data for the translatePlaceholder test.
     * @return array
     */
    public function provideTranslatePlaceholder(): array
    {
        return [
            ['abc', 'def', 'ghi', 'placeholder def ghi', 'jkl', 'jkl'],
            ['abc', 'def', 'ghi', 'placeholder def ghi', 'placeholder def ghi', null],
        ];
    }

    /**
     * Tests the translatePlaceholder method.
     * @param string $locale
     * @param string $section
     * @param string $name
     * @param string $expectedLanguageKey
     * @param string $resultTranslate
     * @param string|null $expectedResult
     * @throws ReflectionException
     * @covers ::translatePlaceholder
     * @dataProvider provideTranslatePlaceholder
     */
    public function testTranslatePlaceholder(
        string $locale,
        string $section,
        string $name,
        string $expectedLanguageKey,
        string $resultTranslate,
        ?string $expectedResult
    ): void {
        /* @var ZendTranslator|MockObject $placeholderTranslator */
        $placeholderTranslator = $this->getMockBuilder(ZendTranslator::class)
                                      ->setMethods(['setLocale', 'setFallbackLocale', 'translate'])
                                      ->disableOriginalConstructor()
                                      ->getMock();
        $placeholderTranslator->expects($this->once())
                              ->method('setLocale')
                              ->with($locale)
                              ->willReturnSelf();
        $placeholderTranslator->expects($this->once())
                              ->method('setFallbackLocale')
                              ->with('en')
                              ->willReturnSelf();
        $placeholderTranslator->expects($this->once())
                              ->method('translate')
                              ->with($expectedLanguageKey)
                              ->willReturn($resultTranslate);

        /* @var LocaleReader $localeReader */
        $localeReader = $this->createMock(LocaleReader::class);
        /* @var ModRegistry $modRegistry */
        $modRegistry = $this->createMock(ModRegistry::class);

        $translator = new Translator($localeReader, $modRegistry, $placeholderTranslator);
        $result = $this->invokeMethod($translator, 'translatePlaceholder', $locale, $section, $name);
        $this->assertSame($expectedResult, $result);
    }
}
