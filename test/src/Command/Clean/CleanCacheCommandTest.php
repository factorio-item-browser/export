<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Command\Clean;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Export\Cache\AbstractCache;
use FactorioItemBrowser\Export\Command\Clean\CleanCacheCommand;
use FactorioItemBrowser\Export\Constant\ParameterName;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ZF\Console\Route;

/**
 * The PHPUnit test of the ClearCacheCommand class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Command\Clean\CleanCacheCommand
 */
class CleanCacheCommandTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests the constructing.
     * @covers ::__construct
     * @throws ReflectionException
     */
    public function testConstruct(): void
    {
        /* @var AbstractCache $cache1 */
        $cache1 = $this->createMock(AbstractCache::class);
        /* @var AbstractCache $cache2 */
        $cache2 = $this->createMock(AbstractCache::class);

        $caches = [$cache1, $cache2];

        $command = new CleanCacheCommand($caches);
        $this->assertSame($caches, $this->extractProperty($command, 'caches'));
    }

    /**
     * Provides the data for the invoke test.
     * @return array
     */
    public function provideInvoke(): array
    {
        return [
            ['', 'clear'],
            ['abc', 'clearMod'],
        ];
    }

    /**
     * Tests the execute() method.
     * @param string $modName
     * @param string $expectedMethod
     * @throws ReflectionException
     * @covers ::execute
     * @dataProvider provideInvoke
     */
    public function testExecute(string $modName, string $expectedMethod): void
    {
        /* @var Route|MockObject $route */
        $route = $this->getMockBuilder(Route::class)
                      ->setMethods(['getMatchedParam'])
                      ->disableOriginalConstructor()
                      ->getMock();
        $route->expects($this->once())
              ->method('getMatchedParam')
              ->with(ParameterName::MOD_NAME, '')
              ->willReturn($modName);

        /* @var AbstractCache|MockObject $cache */
        $cache = $this->getMockBuilder(AbstractCache::class)
                      ->setMethods(['clearMod', 'clear'])
                      ->disableOriginalConstructor()
                      ->getMockForAbstractClass();
        $cache->expects($expectedMethod === 'clearMod' ? $this->once() : $this->never())
              ->method('clearMod')
              ->with($modName);
        $cache->expects($expectedMethod === 'clear' ? $this->once() : $this->never())
              ->method('clear');

        $command = new CleanCacheCommand([$cache]);
        $this->invokeMethod($command, 'execute', $route);
    }
}
