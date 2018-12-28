<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Command\Export;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Export\Combination\CombinationCreator;
use FactorioItemBrowser\Export\Command\Export\ExportModCommand;
use FactorioItemBrowser\Export\Console\Console;
use FactorioItemBrowser\Export\Constant\CommandName;
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
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        /* @var CombinationCreator $combinationCreator */
        $combinationCreator = $this->createMock(CombinationCreator::class);
        /* @var ModRegistry $modRegistry */
        $modRegistry = $this->createMock(ModRegistry::class);

        $command = new ExportModCommand($combinationCreator, $modRegistry);

        $this->assertSame($combinationCreator, $this->extractProperty($command, 'combinationCreator'));
        $this->assertSame($modRegistry, $this->extractProperty($command, 'modRegistry'));
    }

    /**
     * Tests the processMod method.
     * @throws ReflectionException
     * @covers ::processMod
     */
    public function testProcessMod(): void
    {
        $mod = (new Mod())->setName('abc');
        $numberOfOptionalMods = 2;

        /* @var CombinationCreator|MockObject $combinationCreator */
        $combinationCreator = $this->getMockBuilder(CombinationCreator::class)
                                   ->setMethods(['setupForMod', 'getNumberOfOptionalMods'])
                                   ->disableOriginalConstructor()
                                   ->getMock();
        $combinationCreator->expects($this->once())
                           ->method('setupForMod')
                           ->with($mod);
        $combinationCreator->expects($this->once())
                           ->method('getNumberOfOptionalMods')
                           ->willReturn($numberOfOptionalMods);

        /* @var Console|MockObject $console */
        $console = $this->getMockBuilder(Console::class)
                        ->setMethods(['writeBanner', 'writeAction'])
                        ->disableOriginalConstructor()
                        ->getMock();
        $console->expects($this->once())
                ->method('writeBanner')
                ->with('Exporting Mod: abc', ColorInterface::LIGHT_BLUE);
        $console->expects($this->once())
                ->method('writeAction')
                ->with('Exporting combinations in 3 steps');

        /* @var ModRegistry $modRegistry */
        $modRegistry = $this->createMock(ModRegistry::class);

        /* @var ExportModCommand|MockObject $command */
        $command = $this->getMockBuilder(ExportModCommand::class)
                        ->setMethods(['runCommand'])
                        ->setConstructorArgs([$combinationCreator, $modRegistry])
                        ->getMock();
        $command->expects($this->exactly(6))
                ->method('runCommand')
                ->withConsecutive(
                    [CommandName::EXPORT_MOD_STEP, ['modName' => 'abc', 'step' => 0], $console],
                    [CommandName::EXPORT_MOD_STEP, ['modName' => 'abc', 'step' => 1], $console],
                    [CommandName::EXPORT_MOD_STEP, ['modName' => 'abc', 'step' => 2], $console],
                    [CommandName::EXPORT_MOD_META, ['modName' => 'abc'], $console],
                    [CommandName::REDUCE_MOD, ['modName' => 'abc'], $console],
                    [CommandName::RENDER_MOD_ICONS, ['modName' => 'abc'], $console]
                );
        $this->injectProperty($command, 'console', $console);

        /* @var Route $route */
        $route = $this->createMock(Route::class);

        $this->invokeMethod($command, 'processMod', $route, $mod);
    }
}
