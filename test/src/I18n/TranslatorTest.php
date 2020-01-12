<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\I18n;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\I18n\Translator;
use FactorioItemBrowser\Export\I18n\LocaleReader;
use FactorioItemBrowser\ExportData\Entity\LocalisedString;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Laminas\I18n\Translator\Translator as LaminasTranslator;

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
     * The mocked locale reader.
     * @var LocaleReader&MockObject
     */
    protected $localeReader;

    /**
     * The mocked placeholder translator.
     * @var LaminasTranslator&MockObject
     */
    protected $placeholderTranslator;

    /**
     * Sets up the test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->localeReader = $this->createMock(LocaleReader::class);
        $this->placeholderTranslator = $this->createMock(LaminasTranslator::class);
    }

    /**
     * Tests the constructing.
     * @covers ::__construct
     * @throws ReflectionException
     */
    public function testConstruct(): void
    {
        $translator = new Translator($this->localeReader, $this->placeholderTranslator);

        $this->assertSame($this->localeReader, $this->extractProperty($translator, 'localeReader'));
        $this->assertSame($this->placeholderTranslator, $this->extractProperty($translator, 'placeholderTranslator'));
    }

    /**
     * Tests the loadFromModNames method.
     * @throws ExportException
     * @throws ReflectionException
     * @covers ::loadFromModNames
     */
    public function testLoadFromModNames(): void
    {
        $modNames = ['abc', 'def'];

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

        $this->localeReader->expects($this->exactly(2))
                           ->method('read')
                           ->withConsecutive(
                               [$this->identicalTo('abc')],
                               [$this->identicalTo('def')]
                           )
                           ->willReturnOnConsecutiveCalls(
                               $translations1,
                               $translations2
                           );

        $translator = new Translator($this->localeReader, $this->placeholderTranslator);
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

        /* @var LocalisedString&MockObject $entity */
        $entity = $this->createMock(LocalisedString::class);
        $entity->expects($this->exactly(2))
               ->method('addTranslation')
               ->withConsecutive(
                   [$this->identicalTo('jkl'), $this->identicalTo('stu')],
                   [$this->identicalTo('pqr'), $this->identicalTo('vwx')]
               );

        /* @var Translator&MockObject $translator */
        $translator = $this->getMockBuilder(Translator::class)
                           ->onlyMethods(['translateWithFallback'])
                           ->setConstructorArgs([$this->localeReader, $this->placeholderTranslator])
                           ->getMock();
        $translator->expects($this->exactly(3))
                   ->method('translateWithFallback')
                   ->withConsecutive(
                       [
                           $this->identicalTo('jkl'),
                           $this->identicalTo($type),
                           $this->identicalTo($localisedString),
                           $this->identicalTo($fallbackLocalisedString),
                       ],
                       [
                           $this->identicalTo('mno'),
                           $this->identicalTo($type),
                           $this->identicalTo($localisedString),
                           $this->identicalTo($fallbackLocalisedString),
                       ],
                       [
                           $this->identicalTo('pqr'),
                           $this->identicalTo($type),
                           $this->identicalTo($localisedString),
                           $this->identicalTo($fallbackLocalisedString),
                       ]
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
     * @return array<mixed>
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
     * @param string|array<mixed> $fallbackLocalisedString
     * @param array|string[] $resultsTranslate
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

        /* @var Translator&MockObject $translator */
        $translator = $this->getMockBuilder(Translator::class)
                           ->onlyMethods(['translate'])
                           ->setConstructorArgs([$this->localeReader, $this->placeholderTranslator])
                           ->getMock();
        $translator->expects($this->exactly(count($resultsTranslate)))
                   ->method('translate')
                   ->withConsecutive(
                       [
                           $this->identicalTo($locale),
                           $this->identicalTo($type),
                           $this->identicalTo($localisedString),
                           $this->identicalTo(1),
                       ],
                       [
                           $this->identicalTo($locale),
                           $this->identicalTo($type),
                           $this->identicalTo($fallbackLocalisedString),
                           $this->identicalTo(1),
                       ]
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
     * @return array<mixed>
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
     * @param array|string[] $resultsTranslate
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

        /* @var Translator&MockObject $translator */
        $translator = $this->getMockBuilder(Translator::class)
                           ->onlyMethods(['translateLocalisedString', 'resolveReferences'])
                           ->setConstructorArgs([$this->localeReader, $this->placeholderTranslator])
                           ->getMock();
        $translator->expects($this->exactly(count($resultsTranslate)))
                   ->method('translateLocalisedString')
                   ->withConsecutive(
                       [
                           $this->identicalTo($locale),
                           $this->identicalTo($type),
                           $this->identicalTo($localisedString),
                           $this->identicalTo($level),
                       ],
                       [
                           $this->identicalTo('en'),
                           $this->identicalTo($type),
                           $this->identicalTo($localisedString),
                           $this->identicalTo($level),
                       ]
                   )
                   ->willReturnOnConsecutiveCalls(
                       ...$resultsTranslate
                   );
        $translator->expects($this->once())
                   ->method('resolveReferences')
                   ->with(
                       $this->identicalTo($locale),
                       $this->identicalTo($type),
                       $this->identicalTo($paramResolveReferences)
                   )
                   ->willReturn($resultResolveReferences);

        $result = $this->invokeMethod($translator, 'translate', $locale, $type, $localisedString, $level);
        $this->assertSame($resultResolveReferences, $result);
    }

    /**
     * Provides the data for the translateLocalisedString test.
     * @return array<mixed>
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
     * @param array|string[][] $translations
     * @param string $locale
     * @param string|array<mixed> $localisedString
     * @param string|null $resultTranslateParameters
     * @param string|null $expectedString
     * @param array<string>|null $expectedParameters
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

        /* @var Translator&MockObject $translator */
        $translator = $this->getMockBuilder(Translator::class)
                           ->onlyMethods(['translateParameters'])
                           ->setConstructorArgs([$this->localeReader, $this->placeholderTranslator])
                           ->getMock();
        $translator->expects($resultTranslateParameters === null ? $this->never() : $this->once())
                   ->method('translateParameters')
                   ->with(
                       $this->identicalTo($locale),
                       $this->identicalTo($type),
                       $this->identicalTo($expectedString),
                       $this->identicalTo($expectedParameters),
                       $this->identicalTo($level)
                   )
                   ->willReturn($resultTranslateParameters === null ? '' : $resultTranslateParameters);
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

        /* @var Translator&MockObject $translator */
        $translator = $this->getMockBuilder(Translator::class)
                           ->onlyMethods(['translate'])
                           ->setConstructorArgs([$this->localeReader, $this->placeholderTranslator])
                           ->getMock();
        $translator->expects($this->exactly(2))
                   ->method('translate')
                   ->withConsecutive(
                       [
                           $this->identicalTo($locale),
                           $this->identicalTo($type),
                           $this->identicalTo('pqr'),
                           $this->identicalTo(43),
                       ],
                       [
                           $this->identicalTo($locale),
                           $this->identicalTo($type),
                           $this->identicalTo('stu'),
                           $this->identicalTo(43),
                       ]
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

        /* @var Translator&MockObject $translator */
        $translator = $this->getMockBuilder(Translator::class)
                           ->onlyMethods(['translateReference'])
                           ->setConstructorArgs([$this->localeReader, $this->placeholderTranslator])
                           ->getMock();
        $translator->expects($this->exactly(2))
                   ->method('translateReference')
                   ->withConsecutive(
                       [
                           $this->identicalTo($locale),
                           $this->identicalTo('jkl'),
                           $this->identicalTo($type),
                           $this->identicalTo('mno'),
                       ],
                       [
                           $this->identicalTo($locale),
                           $this->identicalTo('stu'),
                           $this->identicalTo($type),
                           $this->identicalTo('vwx'),
                       ]
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
     * @return array<mixed>
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
     * @param array|string[][] $translations
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
        /* @var Translator&MockObject $translator */
        $translator = $this->getMockBuilder(Translator::class)
                           ->onlyMethods(['translatePlaceholder'])
                           ->setConstructorArgs([$this->localeReader, $this->placeholderTranslator])
                           ->getMock();
        $translator->expects($expectPlaceholder ? $this->once() : $this->never())
                   ->method('translatePlaceholder')
                   ->with($this->identicalTo($locale), $this->identicalTo($section), $this->identicalTo($name))
                   ->willReturn($resultPlaceholder);
        $this->injectProperty($translator, 'translations', $translations);

        $result = $this->invokeMethod($translator, 'translateReference', $locale, $section, $type, $name);
        $this->assertSame($expectedResult, $result);
    }

    /**
     * Provides the data for the translatePlaceholder test.
     * @return array<mixed>
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
        $this->placeholderTranslator->expects($this->once())
                                    ->method('setLocale')
                                    ->with($locale)
                                    ->willReturnSelf();
        $this->placeholderTranslator->expects($this->once())
                                    ->method('setFallbackLocale')
                                    ->with('en')
                                    ->willReturnSelf();
        $this->placeholderTranslator->expects($this->once())
                                    ->method('translate')
                                    ->with($expectedLanguageKey)
                                    ->willReturn($resultTranslate);

        $translator = new Translator($this->localeReader, $this->placeholderTranslator);
        $result = $this->invokeMethod($translator, 'translatePlaceholder', $locale, $section, $name);

        $this->assertSame($expectedResult, $result);
    }
}
