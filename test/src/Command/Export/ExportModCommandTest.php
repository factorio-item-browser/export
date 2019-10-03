<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Command\Export;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Export\Combination\CombinationCreator;
use FactorioItemBrowser\Export\Command\Export\ExportModCommand;
use FactorioItemBrowser\Export\Console\Console;
use FactorioItemBrowser\Export\Constant\CommandName;
use FactorioItemBrowser\Export\Constant\ParameterName;
use FactorioItemBrowser\ExportData\Entity\Mod;
use FactorioItemBrowser\ExportData\Registry\ModRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Zend\Console\ColorInterface;
use ZF\Console\Route;

/**
 * The PHPUnit test of the ExportModCommand class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Command\Export\ExportModCommand
 */
class ExportModCommandTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked combination creator.
     * @var CombinationCreator&MockObject
     */
    protected $combinationCreator;

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

        $this->combinationCreator = $this->createMock(CombinationCreator::class);
        $this->modRegistry = $this->createMock(ModRegistry::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $command = new ExportModCommand($this->combinationCreator, $this->modRegistry);

        $this->assertSame($this->combinationCreator, $this->extractProperty($command, 'combinationCreator'));
        $this->assertSame($this->modRegistry, $this->extractProperty($command, 'modRegistry'));
    }

    /**
     * Tests the processMod method.
     * @throws ReflectionException
     * @covers ::processMod
     */
    public function testProcessMod(): void
    {
        $numberOfOptionalMods = 2;
        $modName = 'abc';

        /* @var Route&MockObject $route */
        $route = $this->createMock(Route::class);

        /* @var Mod&MockObject $mod */
        $mod = $this->createMock(Mod::class);
        $mod->expects($this->once())
            ->method('getName')
            ->willReturn($modName);

        $this->combinationCreator->expects($this->once())
                                ->method('setupForMod')
                                ->with($this->identicalTo($mod));
        $this->combinationCreator->expects($this->once())
                                 ->method('getNumberOfOptionalMods')
                                 ->willReturn($numberOfOptionalMods);

        /* @var Console&MockObject $console */
        $console = $this->createMock(Console::class);
        $console->expects($this->once())
                ->method('writeBanner')
                ->with($this->identicalTo('Exporting Mod: abc'), $this->identicalTo(ColorInterface::LIGHT_BLUE));
        $console->expects($this->once())
                ->method('writeAction')
                ->with($this->identicalTo('Exporting combinations in 3 steps'));

        /* @var ExportModCommand|MockObject $command */
        $command = $this->getMockBuilder(ExportModCommand::class)
                        ->setMethods(['runModCommand'])
                        ->setConstructorArgs([$this->combinationCreator, $this->modRegistry])
                        ->getMock();
        $command->expects($this->exactly(7))
                ->method('runModCommand')
                ->withConsecutive(
                    [
                        $this->identicalTo(CommandName::EXPORT_MOD_STEP),
                        $this->identicalTo($mod),
                        $this->identicalTo([ParameterName::STEP => 0]),
                    ],
                    [
                        $this->identicalTo(CommandName::EXPORT_MOD_STEP),
                        $this->identicalTo($mod),
                        $this->identicalTo([ParameterName::STEP => 1]),
                    ],
                    [
                        $this->identicalTo(CommandName::EXPORT_MOD_STEP),
                        $this->identicalTo($mod),
                        $this->identicalTo([ParameterName::STEP => 2]),
                    ],
                    [
                        $this->identicalTo(CommandName::EXPORT_MOD_META),
                        $this->identicalTo($mod),
                        $this->identicalTo([]),
                    ],
                    [
                        $this->identicalTo(CommandName::EXPORT_MOD_THUMBNAIL),
                        $this->identicalTo($mod),
                        $this->identicalTo([]),
                    ],
                    [
                        $this->identicalTo(CommandName::REDUCE_MOD),
                        $this->identicalTo($mod),
                        $this->identicalTo([]),
                    ],
                    [
                        $this->identicalTo(CommandName::RENDER_MOD_ICONS),
                        $this->identicalTo($mod),
                        $this->identicalTo([]),
                    ]
                );
        $this->injectProperty($command, 'console', $console);

        $this->invokeMethod($command, 'processMod', $route, $mod);
    }

    /**
     * Tests the runModCommand method.
     * @throws ReflectionException
     * @covers ::runModCommand
     */
    public function testRunModCommand(): void
    {
        $commandName = 'abc';
        $modName = 'def';
        $additionalParameters = ['ghi' => 'jkl'];
        $expectedParameters = [
            ParameterName::MOD_NAME => 'def',
            'ghi' => 'jkl',
        ];

        /* @var Mod&MockObject $mod */
        $mod = $this->createMock(Mod::class);
        $mod->expects($this->once())
            ->method('getName')
            ->willReturn($modName);

        /* @var Console&MockObject $console */
        $console = $this->createMock(Console::class);

        /* @var ExportModCommand|MockObject $command */
        $command = $this->getMockBuilder(ExportModCommand::class)
                        ->setMethods(['runCommand'])
                        ->setConstructorArgs([$this->combinationCreator, $this->modRegistry])
                        ->getMock();
        $command->expects($this->once())
                ->method('runCommand')
                ->with(
                    $this->identicalTo($commandName),
                    $this->equalTo($expectedParameters),
                    $this->identicalTo($console)
                );
        $this->injectProperty($command, 'console', $console);

        $this->invokeMethod($command, 'runModCommand', $commandName, $mod, $additionalParameters);
    }
}
