<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Log;

use FactorioItemBrowser\Export\Constant\ConfigKey;
use FactorioItemBrowser\Export\Log\LoggerFactory;
use Interop\Container\ContainerInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * The PHPUnit test of the LoggerFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Export\Log\LoggerFactory
 */
class LoggerFactoryTest extends TestCase
{
    public function test(): void
    {
        $config = [
            ConfigKey::PROJECT => [
                ConfigKey::EXPORT => [
                    ConfigKey::DIRECTORIES => [
                        ConfigKey::DIRECTORY_LOGS => 'abc',
                    ],
                ],
            ],
        ];

        $expectedResult = new Logger('cli');
        $expectedResult->pushHandler(new StreamHandler(sprintf('abc/cli_%s.log', date('Y-m-d'))));

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->any())
                  ->method('get')
                  ->willReturnMap([
                      ['config', $config],
                  ]);

        $instance = new LoggerFactory();
        $result = $instance($container, LoggerInterface::class);

        $this->assertEquals($expectedResult, $result);
    }
}
