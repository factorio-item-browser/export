<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Parser;

use BluePsyduck\FactorioTranslator\Exception\NoSupportedLoaderException;
use BluePsyduck\FactorioTranslator\Translator;
use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Mod\ModFileManager;
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

    /** @var ModFileManager&MockObject */
    private ModFileManager $modFileManager;
    /** @var Translator&MockObject */
    private Translator $translator;

    /**
     * Sets up the test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->modFileManager = $this->createMock(ModFileManager::class);
        $this->translator = $this->createMock(Translator::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $parser = new TranslationParser($this->modFileManager, $this->translator);

        $this->assertSame($this->modFileManager, $this->extractProperty($parser, 'modFileManager'));
        $this->assertSame($this->translator, $this->extractProperty($parser, 'translator'));
    }

    /**
     * Tests the prepare method.
     * @throws NoSupportedLoaderException
     * @covers ::prepare
     */
    public function testPrepare(): void
    {
        $dump = new Dump();
        $dump->modNames = ['abc', 'def'];

        $this->modFileManager->expects($this->exactly(3))
                             ->method('getLocalDirectory')
                             ->withConsecutive(
                                 [$this->identicalTo('core')],
                                 [$this->identicalTo('abc')],
                                 [$this->identicalTo('def')],
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

        $parser = new TranslationParser($this->modFileManager, $this->translator);
        $parser->prepare($dump);
    }

    /**
     * Tests the parse method.
     * @throws ExportException
     * @covers ::parse
     */
    public function testParse(): void
    {
        /* @var Dump&MockObject $dump */
        $dump = $this->createMock(Dump::class);
        /* @var Combination&MockObject $combination */
        $combination = $this->createMock(Combination::class);

        $parser = new TranslationParser($this->modFileManager, $this->translator);
        $parser->parse($dump, $combination);

        $this->addToAssertionCount(1);
    }

    /**
     * Tests the validate method.
     * @throws ExportException
     * @covers ::validate
     */
    public function testValidate(): void
    {
        /* @var Combination&MockObject $combination */
        $combination = $this->createMock(Combination::class);

        $parser = new TranslationParser($this->modFileManager, $this->translator);
        $parser->validate($combination);

        $this->addToAssertionCount(1);
    }

    /**
     * Tests the translate method.
     * @covers ::translate
     */
    public function testTranslate(): void
    {
        $locales = ['abc', 'def', 'ghi'];
        $translation = 'foo';
        $secondaryTranslation = 'bar';

        $entity = $this->createMock(LocalisedString::class);
        $entity->expects($this->exactly(2))
               ->method('addTranslation')
               ->withConsecutive(
                   [$this->identicalTo('abc'), $this->identicalTo('jkl')],
                   [$this->identicalTo('def'), $this->identicalTo('mno')],
               );

        $this->translator->expects($this->once())
                         ->method('getAllLocales')
                         ->willReturn($locales);
        $this->translator->expects($this->exactly(5))
                         ->method('translate')
                         ->withConsecutive(
                             [$this->identicalTo('abc'), $this->identicalTo($translation)],
                             [$this->identicalTo('def'), $this->identicalTo($translation)],
                             [$this->identicalTo('def'), $this->identicalTo($secondaryTranslation)],
                             [$this->identicalTo('ghi'), $this->identicalTo($translation)],
                             [$this->identicalTo('ghi'), $this->identicalTo($secondaryTranslation)],
                         )
                         ->willReturnOnConsecutiveCalls(
                             'jkl',
                             '',
                             'mno',
                             '',
                             '',
                         );

        $parser = new TranslationParser($this->modFileManager, $this->translator);
        $parser->translate($entity, $translation, $secondaryTranslation);
    }
}
