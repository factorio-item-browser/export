<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\AutoWire\Resolver;

use FactorioItemBrowser\Export\AutoWire\Resolver\ConfigDirectoryResolver;
use FactorioItemBrowser\Export\Constant\ConfigKey;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

/**
 * The PHPUnit test of the ClientFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Export\AutoWire\Resolver\ConfigDirectoryResolver
 */
class ConfigDirectoryResolverTest extends TestCase
{
    /**
     * @throws ContainerExceptionInterface
     */
    public function testResolve(): void
    {
        $config = [
            ConfigKey::MAIN => [
                ConfigKey::DIRECTORIES => [
                    'abc' => 'test/asset',
                ]
            ],
        ];
        $expectedResult = realpath('test/asset');

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
                  ->method('get')
                  ->with($this->identicalTo('config'))
                  ->willReturn($config);

        $instance = new ConfigDirectoryResolver('abc');

        $result = $instance->resolve($container);
        $this->assertEquals($expectedResult, $result);
    }

    public function testSerialize(): void
    {
        $instance = new ConfigDirectoryResolver('abc');

        $result = unserialize(serialize($instance));
        $this->assertEquals($instance, $result);
    }
}
