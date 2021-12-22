<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\AutoWire\Attribute;

use FactorioItemBrowser\Export\AutoWire\Attribute\ReadDirectoryFromConfig;
use FactorioItemBrowser\Export\AutoWire\Resolver\ConfigDirectoryResolver;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the ClientFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Export\AutoWire\Attribute\ReadDirectoryFromConfig
 */
class ReadDirectoryFromConfigTest extends TestCase
{
    public function testCreateResolver(): void
    {
        $key = 'abc';
        $expectedResult = new ConfigDirectoryResolver($key);

        $instance = new ReadDirectoryFromConfig($key);
        $result = $instance->createResolver();

        $this->assertEquals($expectedResult, $result);
    }
}
