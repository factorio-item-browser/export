<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Translator\Processor;

use FactorioItemBrowser\Export\Translator\Processor\RichTextEraser;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the RichTextEraser class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Translator\Processor\RichTextEraser
 */
class RichTextEraserTest extends TestCase
{
    /**
     * Provides the data for the process test.
     * @return array<mixed>
     */
    public function provideProcess(): array
    {
        return [
            ['abc def', 'abc def'],
            ['abc[item=fail]def', 'abcdef'],
            ['abc [item=fail] def', 'abc def'],
            ['abc [color=red] def [font=bold]ghi[/font] jkl [/color] mno', 'abc def ghi jkl mno'],
            ['abc [item=fail], def', 'abc, def'],
            ['abc [item=fail] (def)', 'abc (def)'],
        ];
    }

    /**
     * Tests the process method.
     * @param string $string
     * @param string $expectedResult
     * @covers ::process
     * @dataProvider provideProcess
     */
    public function testProcess(string $string, string $expectedResult): void
    {
        $locale = 'foo';
        $parameters = ['bar'];

        $processor = new RichTextEraser();
        $result = $processor->process($locale, $string, $parameters);

        $this->assertSame($expectedResult, $result);
    }
}
