<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Utils;

use FactorioItemBrowser\Export\Utils\ConsoleUtils;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the ConsoleUtils class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Utils\ConsoleUtils
 */
class ConsoleUtilsTest extends TestCase
{
    /**
     * Provides the data for the formatModName test.
     * @return array
     */
    public function provideFormatModName(): array
    {
        return [

            ['abc', '', '                                                             abc'],
            ['abc-def-ghi-jkl', '', '                                                 abc-def-ghi-jkl'],
            ['abc', 'def', '                                                             abcdef'],
        ];
    }

    /**
     * Tests the formatModName method.
     * @param string $modName
     * @param string $suffix
     * @param string $expectedResult
     * @covers ::formatModName
     * @dataProvider provideFormatModName
     */
    public function testFormatModName(string $modName, string $suffix, string $expectedResult): void
    {
        $result = ConsoleUtils::formatModName($modName, $suffix);
        $this->assertSame($expectedResult, $result);
    }

    /**
     * Provides the data for the formatVersion test.
     * @return array
     */
    public function provideFormatVersion(): array
    {
        return [
            ['1.2.3', false, '1.2.3     '],
            ['1.2.3', true, '     1.2.3'],
            ['1', false, '1.0.0     '],
            ['', false, '          ']
        ];
    }
    /**
     * Tests the formatVersion method.
     * @param string $version
     * @param bool $padLeft
     * @param string $expectedResult
     * @covers ::formatVersion
     * @dataProvider provideFormatVersion
     */
    public function testFormatVersion(string $version, bool $padLeft, string $expectedResult): void
    {
        $result = ConsoleUtils::formatVersion($version, $padLeft);
        $this->assertSame($expectedResult, $result);
    }
}
