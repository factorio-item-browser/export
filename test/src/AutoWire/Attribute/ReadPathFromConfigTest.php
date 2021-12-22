<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\AutoWire\Attribute;

use FactorioItemBrowser\Export\AutoWire\Attribute\ReadPathFromConfig;
use FactorioItemBrowser\Export\AutoWire\Resolver\ConfigPathResolver;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the ReadPathFromConfig class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Export\AutoWire\Attribute\ReadPathFromConfig
 */
class ReadPathFromConfigTest extends TestCase
{
    public function testCreateResolver(): void
    {
        $keys = ['abc', 'def'];
        $expectedResult = new ConfigPathResolver($keys);

        $instance = new ReadPathFromConfig(...$keys);
        $result = $instance->createResolver();

        $this->assertEquals($expectedResult, $result);
    }
}
