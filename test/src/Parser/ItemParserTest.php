<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Parser;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Export\I18n\Translator;
use FactorioItemBrowser\Export\Parser\IconParser;
use FactorioItemBrowser\Export\Parser\ItemParser;
use FactorioItemBrowser\ExportData\Registry\EntityRegistry;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the ItemParser class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Parser\ItemParser
 */
class ItemParserTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests the constructing.
     * @covers ::__construct
     * @throws ReflectionException
     */
    public function testConstruct(): void
    {
        /* @var IconParser $iconParser */
        $iconParser = $this->createMock(IconParser::class);
        /* @var EntityRegistry $itemRegistry */
        $itemRegistry = $this->createMock(EntityRegistry::class);
        /* @var Translator $translator */
        $translator = $this->createMock(Translator::class);


        $parser = new ItemParser($iconParser, $itemRegistry, $translator);
        $this->assertSame($iconParser, $this->extractProperty($parser, 'iconParser'));
        $this->assertSame($itemRegistry, $this->extractProperty($parser, 'itemRegistry'));
        $this->assertSame($translator, $this->extractProperty($parser, 'translator'));
    }
}
