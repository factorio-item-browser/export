<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Command;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Export\Command\AbstractModCommand;
use FactorioItemBrowser\Export\Constant\ParameterName;
use FactorioItemBrowser\Export\Exception\CommandException;
use FactorioItemBrowser\ExportData\Entity\Mod;
use FactorioItemBrowser\ExportData\Registry\ModRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ZF\Console\Route;

/**
 * The PHPUnit test of the AbstractProcessModCommand class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Command\AbstractModCommand
 */
class AbstractModCommandTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked mod registry.
     * @var ModRegistry&MockObject
     */
    protected $modRegistry;

    /**
     * Sets up the test case.
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->modRegistry = $this->createMock(ModRegistry::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        /* @var AbstractModCommand&MockObject $command */
        $command = $this->getMockBuilder(AbstractModCommand::class)
                        ->setConstructorArgs([$this->modRegistry])
                        ->getMockForAbstractClass();

        $this->assertSame($this->modRegistry, $this->extractProperty($command, 'modRegistry'));
    }

    /**
     * Tests the execute method.
     * @throws ReflectionException
     * @covers ::execute
     */
    public function testExecute(): void
    {
        $modName = 'abc';
        $mod = (new Mod())->setName('def');

        /* @var Route&MockObject $route */
        $route = $this->createMock(Route::class);
        $route->expects($this->once())
              ->method('getMatchedParam')
              ->with($this->identicalTo(ParameterName::MOD_NAME), $this->identicalTo(''))
              ->willReturn($modName);

        /* @var AbstractModCommand|MockObject $command */
        $command = $this->getMockBuilder(AbstractModCommand::class)
                        ->setMethods(['fetchMod', 'processMod'])
                        ->setConstructorArgs([$this->modRegistry])
                        ->getMockForAbstractClass();
        $command->expects($this->once())
                ->method('fetchMod')
                ->with($this->identicalTo($modName))
                ->willReturn($mod);
        $command->expects($this->once())
                ->method('processMod')
                ->with($this->identicalTo($route), $this->identicalTo($mod));

        $this->invokeMethod($command, 'execute', $route);
    }

    /**
     * Tests the fetchMod method.
     * @throws ReflectionException
     * @covers ::fetchMod
     */
    public function testFetchMod(): void
    {
        $modName = 'abc';

        /* @var Mod&MockObject $mod */
        $mod = $this->createMock(Mod::class);

        $this->modRegistry->expects($this->once())
                          ->method('get')
                          ->with($this->identicalTo($modName))
                          ->willReturn($mod);

        /* @var AbstractModCommand&MockObject $command */
        $command = $this->getMockBuilder(AbstractModCommand::class)
                        ->setConstructorArgs([$this->modRegistry])
                        ->getMockForAbstractClass();

        $result = $this->invokeMethod($command, 'fetchMod', $modName);

        $this->assertSame($mod, $result);
    }

    /**
     * Tests the fetchMod method without an actual mod.
     * @throws ReflectionException
     * @covers ::fetchMod
     */
    public function testFetchModWithoutMod(): void
    {
        $modName = 'abc';
        $mod = null;

        $this->modRegistry->expects($this->once())
                          ->method('get')
                          ->with($this->identicalTo($modName))
                          ->willReturn($mod);

        $this->expectException(CommandException::class);
        $this->expectExceptionCode(404);

        /* @var AbstractModCommand&MockObject $command */
        $command = $this->getMockBuilder(AbstractModCommand::class)
                        ->setConstructorArgs([$this->modRegistry])
                        ->getMockForAbstractClass();

        $this->invokeMethod($command, 'fetchMod', $modName);
    }

    /**
     * Tests the persistMod method.
     * @throws ReflectionException
     * @covers ::persistMod
     */
    public function testPersistMod(): void
    {
        /* @var Mod&MockObject $mod */
        $mod = $this->createMock(Mod::class);

        $this->modRegistry->expects($this->once())
                          ->method('set')
                          ->with($this->identicalTo($mod));
        $this->modRegistry->expects($this->once())
                          ->method('saveMods');

        /* @var AbstractModCommand&MockObject $command */
        $command = $this->getMockBuilder(AbstractModCommand::class)
                        ->setConstructorArgs([$this->modRegistry])
                        ->getMockForAbstractClass();

        $this->invokeMethod($command, 'persistMod', $mod);
    }
}
