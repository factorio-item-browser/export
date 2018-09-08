<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\I18n;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Export\I18n\Translator;
use FactorioItemBrowser\Export\Mod\LocaleReader;
use FactorioItemBrowser\ExportData\Registry\ModRegistry;
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
}
