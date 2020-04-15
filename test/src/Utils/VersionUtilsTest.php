<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Utils;

use FactorioItemBrowser\Export\Utils\VersionUtils;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the VersionUtils class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Utils\VersionUtils
 */
class VersionUtilsTest extends TestCase
{
    /**
     * Provides the data for the normalize test.
     * @return array<mixed>
     */
    public function provideNormalize(): array
    {
        return [
            ['1.2.3', '1.2.3'],
            ['1.2', '1.2.0'],
            ['1', '1.0.0'],
        ];
    }

    /**
     * Tests the normalize method.
     * @param string $version
     * @param string $expectedResult
     * @covers ::normalize
     * @covers ::splitVersion
     * @dataProvider provideNormalize
     */
    public function testNormalize(string $version, string $expectedResult): void
    {
        $result = VersionUtils::normalize($version);
        $this->assertSame($expectedResult, $result);
    }

    /**
     * Provides the data for the compare test.
     * @return array<mixed>
     */
    public function provideCompare(): array
    {
        return [
            ['1.2.3', '3.2.1', -1],
            ['1.2.3', '1.2.4', -1],
            ['1.2.3', '1.2.3', 0],
            ['1.2.3', '1.2.2', 1],
            ['1.2.3', '1.0.0', 1],
            ['1.2.3', '1', 1],
        ];
    }

    /**
     * Tests the compare method.
     * @param string $leftVersion
     * @param string $rightVersion
     * @param int $expectedResult
     * @covers ::compare
     * @covers ::splitVersion
     * @dataProvider provideCompare
     */
    public function testCompare(string $leftVersion, string $rightVersion, int $expectedResult): void
    {
        $result = VersionUtils::compare($leftVersion, $rightVersion);
        $this->assertSame($expectedResult, $result);
    }

    /**
     * Provides the data for the getGreater test.
     * @return array<mixed>
     */
    public function provideGetGreater(): array
    {
        return [
            ['1.2.3', '3.2.1', '3.2.1'],
            ['1.2.3', '1.2.4', '1.2.4'],
            ['1.2.3', '1.2.3', '1.2.3'],
            ['1.2.3', '1.2.2', '1.2.3'],
            ['1.2.3', '1.0.0', '1.2.3'],
            ['1.2.3', '2', '2.0.0'],
        ];
    }

    /**
     * Tests the getGreater method.
     * @param string $leftVersion
     * @param string $rightVersion
     * @param string $expectedResult
     * @covers ::getGreater
     * @dataProvider provideGetGreater
     */
    public function testGetGreater(string $leftVersion, string $rightVersion, string $expectedResult): void
    {
        $result = VersionUtils::getGreater($leftVersion, $rightVersion);
        $this->assertSame($expectedResult, $result);
    }
}
