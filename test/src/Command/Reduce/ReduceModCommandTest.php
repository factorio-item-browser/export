<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Command\Reduce;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Export\Command\Reduce\ReduceModCommand;
use FactorioItemBrowser\Export\Console\Console;
use FactorioItemBrowser\Export\Reducer\Mod\ModReducerManager;
use FactorioItemBrowser\ExportData\Entity\Mod;
use FactorioItemBrowser\ExportData\Registry\ModRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ZF\Console\Route;

/**
 * The PHPUnit test of the ReduceModCommand class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Command\Reduce\ReduceModCommand
 */
class ReduceModCommandTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked mod reducer manager.
     * @var ModReducerManager&MockObject
     */
    protected $modReducerManager;

    /**
     * The mocked raw mod registry.
     * @var ModRegistry&MockObject
     */
    protected $rawModRegistry;

    /**
     * The mocked reduced mod registry.
     * @var ModRegistry&MockObject
     */
    protected $reducedModRegistry;

    /**
     * Sets up the test case.
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->modReducerManager = $this->createMock(ModReducerManager::class);
        $this->rawModRegistry = $this->createMock(ModRegistry::class);
        $this->reducedModRegistry = $this->createMock(ModRegistry::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $command = new ReduceModCommand($this->modReducerManager, $this->rawModRegistry, $this->reducedModRegistry);

        $this->assertSame($this->modReducerManager, $this->extractProperty($command, 'modReducerManager'));
        $this->assertSame($this->rawModRegistry, $this->extractProperty($command, 'modRegistry'));
        $this->assertSame($this->reducedModRegistry, $this->extractProperty($command, 'reducedModRegistry'));
    }

    /**
     * Tests the processMod method.
     * @throws ReflectionException
     * @covers ::processMod
     */
    public function testProcessMod(): void
    {
        $modName = 'abc';

        /* @var Mod&MockObject $reducedMod */
        $reducedMod = $this->createMock(Mod::class);
        /* @var Route&MockObject $route */
        $route = $this->createMock(Route::class);

        /* @var Mod&MockObject $rawMod */
        $rawMod = $this->createMock(Mod::class);
        $rawMod->expects($this->once())
               ->method('getName')
               ->willReturn($modName);

        /* @var Console&MockObject $console */
        $console = $this->createMock(Console::class);
        $console->expects($this->once())
                ->method('writeAction')
                ->with($this->identicalTo('Reducing mod abc'));

        $this->modReducerManager->expects($this->once())
                                ->method('reduce')
                                ->with($this->identicalTo($rawMod))
                                ->willReturn($reducedMod);

        $this->reducedModRegistry->expects($this->once())
                                 ->method('set')
                                 ->with($this->identicalTo($reducedMod));
        $this->reducedModRegistry->expects($this->once())
                                 ->method('saveMods');

        $command = new ReduceModCommand($this->modReducerManager, $this->rawModRegistry, $this->reducedModRegistry);
        $this->injectProperty($command, 'console', $console);

        $this->invokeMethod($command, 'processMod', $route, $rawMod);
    }
}
