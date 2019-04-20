<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Reducer\Mod;

use FactorioItemBrowser\Export\Reducer\Mod\CombinationReducer;
use FactorioItemBrowser\Export\Reducer\Mod\ModReducerManager;
use FactorioItemBrowser\Export\Reducer\Mod\ModReducerManagerFactory;
use FactorioItemBrowser\Export\Reducer\Mod\ThumbnailReducer;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the ModReducerManagerFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Reducer\Mod\ModReducerManagerFactory
 */
class ModReducerManagerFactoryTest extends TestCase
{
    /**
     * Tests the invoking.
     * @throws ReflectionException
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        /* @var CombinationReducer&MockObject $combinationReducer */
        $combinationReducer = $this->createMock(CombinationReducer::class);
        /* @var ThumbnailReducer&MockObject $thumbnailReducer */
        $thumbnailReducer = $this->createMock(ThumbnailReducer::class);

        $reducers = [$combinationReducer, $thumbnailReducer];
        $expectedResult = new ModReducerManager($reducers);

        /* @var ContainerInterface&MockObject $container */
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->exactly(2))
                  ->method('get')
                  ->withConsecutive(
                      [$this->identicalTo(CombinationReducer::class)],
                      [$this->identicalTo(ThumbnailReducer::class)]
                  )
                  ->willReturnOnConsecutiveCalls(
                      $combinationReducer,
                      $thumbnailReducer
                  );

        $factory = new ModReducerManagerFactory();
        $result = $factory($container, ModReducerManager::class);

        $this->assertEquals($expectedResult, $result);
    }
}
