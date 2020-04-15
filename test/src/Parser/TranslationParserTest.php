<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Parser;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\I18n\Translator;
use FactorioItemBrowser\Export\Parser\TranslationParser;
use FactorioItemBrowser\ExportData\Entity\Combination;
use FactorioItemBrowser\ExportData\Entity\LocalisedString;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the TranslationParser class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Parser\TranslationParser
 */
class TranslationParserTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked translator.
     * @var Translator&MockObject
     */
    protected $translator;

    /**
     * Sets up the test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->translator = $this->createMock(Translator::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $parser = new TranslationParser($this->translator);

        $this->assertSame($this->translator, $this->extractProperty($parser, 'translator'));
    }

    /**
     * Tests the prepare method.
     * @throws ExportException
     * @covers ::prepare
     */
    public function testPrepare(): void
    {
        $modNames = ['abc', 'def'];

        /* @var Dump&MockObject $dump */
        $dump = $this->createMock(Dump::class);
        $dump->expects($this->once())
             ->method('getModNames')
             ->willReturn($modNames);

        $this->translator->expects($this->once())
                         ->method('loadFromModNames')
                         ->with($this->identicalTo($modNames));

        $parser = new TranslationParser($this->translator);
        $parser->prepare($dump);
    }

    /**
     * Tests the parse method.
     * @covers ::parse
     */
    public function testParse(): void
    {
        /* @var Dump&MockObject $dump */
        $dump = $this->createMock(Dump::class);
        /* @var Combination&MockObject $combination */
        $combination = $this->createMock(Combination::class);

        $parser = new TranslationParser($this->translator);
        $parser->parse($dump, $combination);

        $this->addToAssertionCount(1);
    }

    /**
     * Tests the validate method.
     * @covers ::validate
     */
    public function testValidate(): void
    {
        /* @var Combination&MockObject $combination */
        $combination = $this->createMock(Combination::class);

        $parser = new TranslationParser($this->translator);
        $parser->validate($combination);

        $this->addToAssertionCount(1);
    }

    /**
     * Tests the translateNames method.
     * @covers ::translateNames
     */
    public function testTranslateNames(): void
    {
        $translation = ['abc'];
        $secondaryTranslation = ['def'];

        /* @var LocalisedString&MockObject $names */
        $names = $this->createMock(LocalisedString::class);

        $this->translator->expects($this->once())
                         ->method('addTranslationsToEntity')
                         ->with(
                             $this->identicalTo($names),
                             $this->identicalTo('name'),
                             $this->identicalTo($translation),
                             $this->identicalTo($secondaryTranslation)
                         );

        $parser = new TranslationParser($this->translator);
        $parser->translateNames($names, $translation, $secondaryTranslation);
    }

    /**
     * Tests the translateDescriptions method.
     * @covers ::translateDescriptions
     */
    public function testTranslateDescriptions(): void
    {
        $translation = ['abc'];
        $secondaryTranslation = ['def'];

        /* @var LocalisedString&MockObject $descriptions */
        $descriptions = $this->createMock(LocalisedString::class);

        $this->translator->expects($this->once())
                         ->method('addTranslationsToEntity')
                         ->with(
                             $this->identicalTo($descriptions),
                             $this->identicalTo('description'),
                             $this->identicalTo($translation),
                             $this->identicalTo($secondaryTranslation)
                         );

        $parser = new TranslationParser($this->translator);
        $parser->translateDescriptions($descriptions, $translation, $secondaryTranslation);
    }

    /**
     * Tests the translateModNames method.
     * @covers ::translateModNames
     */
    public function testTranslateModNames(): void
    {
        $modName = 'abc';

        /* @var LocalisedString&MockObject $descriptions */
        $descriptions = $this->createMock(LocalisedString::class);

        $this->translator->expects($this->once())
                         ->method('addTranslationsToEntity')
                         ->with(
                             $this->identicalTo($descriptions),
                             $this->identicalTo('mod-name'),
                             $this->identicalTo(['mod-name.abc']),
                             $this->isNull()
                         );

        $parser = new TranslationParser($this->translator);
        $parser->translateModNames($descriptions, $modName);
    }

    /**
     * Tests the translateModDescriptions method.
     * @covers ::translateModDescriptions
     */
    public function testTranslateModDescriptions(): void
    {
        $modName = 'abc';

        /* @var LocalisedString&MockObject $descriptions */
        $descriptions = $this->createMock(LocalisedString::class);

        $this->translator->expects($this->once())
                         ->method('addTranslationsToEntity')
                         ->with(
                             $this->identicalTo($descriptions),
                             $this->identicalTo('mod-description'),
                             $this->identicalTo(['mod-description.abc']),
                             $this->isNull()
                         );

        $parser = new TranslationParser($this->translator);
        $parser->translateModDescriptions($descriptions, $modName);
    }
}
