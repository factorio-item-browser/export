<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Reducer\Mod;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Export\Exception\ReducerException;
use FactorioItemBrowser\Export\Reducer\Mod\ModReducerInterface;
use FactorioItemBrowser\Export\Reducer\Mod\ModReducerManager;
use FactorioItemBrowser\ExportData\Entity\Mod;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the ModReducerManager class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Reducer\Mod\ModReducerManager
 */
class ModReducerManagerTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $reducers = [
            $this->createMock(ModReducerInterface::class),
            $this->createMock(ModReducerInterface::class),
        ];

        $manager = new ModReducerManager($reducers);

        $this->assertSame($reducers, $this->extractProperty($manager, 'reducers'));
    }

    /**
     * Tests the reduce method.
     * @throws ReducerException
     * @throws ReflectionException
     * @covers ::reduce
     */
    public function testReduce(): void
    {
        /* @var Mod&MockObject $mod */
        $mod = $this->createMock(Mod::class);

        /* @var ModReducerManager&MockObject $manager */
        $manager = $this->getMockBuilder(ModReducerManager::class)
                        ->setMethods(['reduceMod'])
                        ->setConstructorArgs([[]])
                        ->getMock();
        $manager->expects($this->once())
                ->method('reduceMod')
                ->with($this->callback(function (Mod $passedMod) use ($mod): bool {
                    $this->assertEquals($mod, $passedMod);
                    $this->assertNotSame($mod, $passedMod);
                    return true;
                }));

        $result = $manager->reduce($mod);

        $this->assertEquals($mod, $result);
        $this->assertNotSame($mod, $result);
    }

    /**
     * Tests the reduceMod method.
     * @throws ReflectionException
     * @covers ::reduceMod
     */
    public function testReduceMod(): void
    {
        /* @var Mod&MockObject $mod */
        $mod = $this->createMock(Mod::class);

        /* @var ModReducerInterface&MockObject $reducer1 */
        $reducer1 = $this->createMock(ModReducerInterface::class);
        $reducer1->expects($this->once())
                 ->method('reduce')
                 ->with($this->identicalTo($mod));

        /* @var ModReducerInterface&MockObject $reducer2 */
        $reducer2 = $this->createMock(ModReducerInterface::class);
        $reducer2->expects($this->once())
                 ->method('reduce')
                 ->with($this->identicalTo($mod));

        $manager = new ModReducerManager([$reducer1, $reducer2]);

        $this->invokeMethod($manager, 'reduceMod', $mod);
    }
}
