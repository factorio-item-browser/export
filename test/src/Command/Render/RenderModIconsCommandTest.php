<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Command\Render;

use BluePsyduck\TestHelper\ReflectionTrait;
use BluePsyduck\SymfonyProcessManager\ProcessManager;
use FactorioItemBrowser\Export\Command\Render\RenderModIconsCommand;
use FactorioItemBrowser\Export\Console\Console;
use FactorioItemBrowser\Export\Constant\CommandName;
use FactorioItemBrowser\Export\Constant\ParameterName;
use FactorioItemBrowser\Export\Exception\CommandException;
use FactorioItemBrowser\ExportData\Entity\Mod;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;
use FactorioItemBrowser\ExportData\Registry\EntityRegistry;
use FactorioItemBrowser\ExportData\Registry\ModRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Symfony\Component\Process\Process;
use ZF\Console\Route;

/**
 * The PHPUnit test of the RenderModIconsCommand class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Command\Render\RenderModIconsCommand
 */
class RenderModIconsCommandTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked combination registry.
     * @var EntityRegistry&MockObject
     */
    protected $combinationRegistry;

    /**
     * The mocked mod registry.
     * @var ModRegistry&MockObject
     */
    protected $modRegistry;

    /**
     * The mocked process manager.
     * @var ProcessManager&MockObject
     */
    protected $processManager;

    /**
     * Sets up the test case.
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->combinationRegistry = $this->createMock(EntityRegistry::class);
        $this->modRegistry = $this->createMock(ModRegistry::class);
        $this->processManager = $this->createMock(ProcessManager::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $command = new RenderModIconsCommand($this->combinationRegistry, $this->modRegistry, $this->processManager);

        $this->assertSame($this->combinationRegistry, $this->extractProperty($command, 'combinationRegistry'));
        $this->assertSame($this->modRegistry, $this->extractProperty($command, 'modRegistry'));
        $this->assertSame($this->processManager, $this->extractProperty($command, 'processManager'));
    }

    /**
     * Tests the processMod method.
     * @throws ReflectionException
     * @covers ::processMod
     */
    public function testProcessMod(): void
    {
        $mod = (new Mod())->setName('abc');
        $iconHashes = ['def', 'ghi'];

        /* @var Route $route */
        $route = $this->createMock(Route::class);

        /* @var Console&MockObject $console */
        $console = $this->createMock(Console::class);
        $console->expects($this->once())
                ->method('writeAction')
                ->with($this->identicalTo('Rendering 2 icons'));

        $this->processManager->expects($this->once())
                             ->method('waitForAllProcesses');

        /* @var RenderModIconsCommand|MockObject $command */
        $command = $this->getMockBuilder(RenderModIconsCommand::class)
                        ->setMethods(['fetchIconHashesOfMod', 'renderThumbnail', 'renderIconsWithHashes'])
                        ->setConstructorArgs([$this->combinationRegistry, $this->modRegistry, $this->processManager])
                        ->getMock();
        $command->expects($this->once())
                ->method('fetchIconHashesOfMod')
                ->with($this->identicalTo($mod))
                ->willReturn($iconHashes);
        $command->expects($this->once())
                ->method('renderThumbnail')
                ->with($this->identicalTo($mod));
        $command->expects($this->once())
                ->method('renderIconsWithHashes')
                ->with($this->identicalTo($iconHashes));
        $this->injectProperty($command, 'console', $console);

        $this->invokeMethod($command, 'processMod', $route, $mod);
    }

    /**
     * Tests the fetchIconHashesOfMod method.
     * @throws ReflectionException
     * @covers ::fetchIconHashesOfMod
     */
    public function testFetchIconHashesOfMod(): void
    {
        /* @var Mod&MockObject $mod */
        $mod = $this->createMock(Mod::class);
        $mod->expects($this->once())
            ->method('getCombinationHashes')
            ->willReturn(['abc', 'def']);

        /* @var Combination&MockObject $combination1 */
        $combination1 = $this->createMock(Combination::class);
        $combination1->expects($this->once())
                     ->method('getIconHashes')
                     ->willReturn(['ghi', 'jkl']);

        /* @var Combination&MockObject $combination2 */
        $combination2 = $this->createMock(Combination::class);
        $combination2->expects($this->once())
                     ->method('getIconHashes')
                     ->willReturn(['ghi', 'mno']);

        $expectedResult = ['ghi', 'jkl', 'mno'];

        /* @var RenderModIconsCommand|MockObject $command */
        $command = $this->getMockBuilder(RenderModIconsCommand::class)
                        ->setMethods(['fetchCombination'])
                        ->setConstructorArgs([$this->combinationRegistry, $this->modRegistry, $this->processManager])
                        ->getMock();
        $command->expects($this->exactly(2))
                ->method('fetchCombination')
                ->withConsecutive(
                    [$this->identicalTo('abc')],
                    [$this->identicalTo('def')]
                )
                ->willReturnOnConsecutiveCalls(
                    $combination1,
                    $combination2
                );

        $result = $this->invokeMethod($command, 'fetchIconHashesOfMod', $mod);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the fetchCombination method.
     * @throws ReflectionException
     * @covers ::fetchCombination
     */
    public function testFetchCombination(): void
    {
        $combinationHash = 'abc';

        /* @var Combination&MockObject $combination */
        $combination = $this->createMock(Combination::class);

        $this->combinationRegistry->expects($this->once())
                                  ->method('get')
                                  ->with($this->identicalTo($combinationHash))
                                  ->willReturn($combination);

        $command = new RenderModIconsCommand($this->combinationRegistry, $this->modRegistry, $this->processManager);
        $result = $this->invokeMethod($command, 'fetchCombination', $combinationHash);

        $this->assertSame($combination, $result);
    }

    /**
     * Tests the fetchCombination method without an actual combination.
     * @throws ReflectionException
     * @covers ::fetchCombination
     */
    public function testFetchCombinationWithoutCombination(): void
    {
        $combinationHash = 'abc';
        $combination = null;

        $this->combinationRegistry->expects($this->once())
                                  ->method('get')
                                  ->with($this->identicalTo($combinationHash))
                                  ->willReturn($combination);

        $this->expectException(CommandException::class);
        $this->expectExceptionCode(404);

        $command = new RenderModIconsCommand($this->combinationRegistry, $this->modRegistry, $this->processManager);
        $this->invokeMethod($command, 'fetchCombination', $combinationHash);
    }

    /**
     * Tests the renderThumbnail method.
     * @throws ReflectionException
     * @covers ::renderThumbnail
     */
    public function testRenderThumbnail(): void
    {
        $thumbnailHash = 'abc';

        /* @var Mod&MockObject $mod */
        $mod = $this->createMock(Mod::class);
        $mod->expects($this->atLeastOnce())
            ->method('getThumbnailHash')
            ->willReturn($thumbnailHash);

        /* @var Process&MockObject $process */
        $process = $this->createMock(Process::class);

        $this->processManager->expects($this->once())
                             ->method('addProcess')
                             ->with($this->identicalTo($process));

        /* @var RenderModIconsCommand|MockObject $command */
        $command = $this->getMockBuilder(RenderModIconsCommand::class)
                        ->setMethods(['createRenderIconProcess'])
                        ->setConstructorArgs([$this->combinationRegistry, $this->modRegistry, $this->processManager])
                        ->getMock();
        $command->expects($this->once())
                ->method('createRenderIconProcess')
                ->with($this->identicalTo($thumbnailHash))
                ->willReturn($process);

        $this->invokeMethod($command, 'renderThumbnail', $mod);
    }

    /**
     * Tests the renderThumbnail method without an actual thumbnail hash.
     * @throws ReflectionException
     * @covers ::renderThumbnail
     */
    public function testRenderThumbnailWithoutHash(): void
    {
        $thumbnailHash = '';

        /* @var Mod&MockObject $mod */
        $mod = $this->createMock(Mod::class);
        $mod->expects($this->atLeastOnce())
            ->method('getThumbnailHash')
            ->willReturn($thumbnailHash);

        $this->processManager->expects($this->never())
                             ->method('addProcess');

        /* @var RenderModIconsCommand|MockObject $command */
        $command = $this->getMockBuilder(RenderModIconsCommand::class)
                        ->setMethods(['createRenderIconProcess'])
                        ->setConstructorArgs([$this->combinationRegistry, $this->modRegistry, $this->processManager])
                        ->getMock();
        $command->expects($this->never())
                ->method('createRenderIconProcess');

        $this->invokeMethod($command, 'renderThumbnail', $mod);
    }


    /**
     * Tests the renderIconsWithHashes method.
     * @throws ReflectionException
     * @covers ::renderIconsWithHashes
     */
    public function testRenderIconsWithHashes(): void
    {
        $iconHashes = ['abc', 'def'];

        /* @var Process&MockObject $process1 */
        $process1 = $this->createMock(Process::class);
        /* @var Process&MockObject $process2 */
        $process2 = $this->createMock(Process::class);

        $this->processManager->expects($this->exactly(2))
                             ->method('addProcess')
                             ->withConsecutive(
                                 [$this->identicalTo($process1)],
                                 [$this->identicalTo($process2)]
                             );

        /* @var RenderModIconsCommand|MockObject $command */
        $command = $this->getMockBuilder(RenderModIconsCommand::class)
                        ->setMethods(['createRenderIconProcess'])
                        ->setConstructorArgs([$this->combinationRegistry, $this->modRegistry, $this->processManager])
                        ->getMock();
        $command->expects($this->exactly(2))
                ->method('createRenderIconProcess')
                ->withConsecutive(
                    [$this->identicalTo('abc')],
                    [$this->identicalTo('def')]
                )
                ->willReturnOnConsecutiveCalls(
                    $process1,
                    $process2
                );

        $this->invokeMethod($command, 'renderIconsWithHashes', $iconHashes);
    }

    /**
     * Tests the createRenderIconProcess method.
     * @throws ReflectionException
     * @covers ::createRenderIconProcess
     */
    public function testCreateRenderIconProcess(): void
    {
        $iconHash = 'abc';
        $expectedParameters = [
            ParameterName::ICON_HASH => $iconHash,
        ];

        /* @var Process&MockObject $process */
        $process = $this->createMock(Process::class);
        /* @var Console&MockObject $console */
        $console = $this->createMock(Console::class);

        /* @var RenderModIconsCommand|MockObject $command */
        $command = $this->getMockBuilder(RenderModIconsCommand::class)
                        ->setMethods(['createCommandProcess'])
                        ->setConstructorArgs([$this->combinationRegistry, $this->modRegistry, $this->processManager])
                        ->getMock();
        $command->expects($this->once())
                ->method('createCommandProcess')
                ->with(
                    $this->identicalTo(CommandName::RENDER_ICON),
                    $this->identicalTo($expectedParameters),
                    $this->identicalTo($console)
                )
                ->willReturn($process);
        $this->injectProperty($command, 'console', $console);

        $result = $this->invokeMethod($command, 'createRenderIconProcess', $iconHash);

        $this->assertSame($process, $result);
    }
}
