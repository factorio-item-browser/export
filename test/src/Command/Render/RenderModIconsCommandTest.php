<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Command\Render;

use BluePsyduck\Common\Test\ReflectionTrait;
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
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        /* @var EntityRegistry $combinationRegistry */
        $combinationRegistry = $this->createMock(EntityRegistry::class);
        /* @var ModRegistry $modRegistry */
        $modRegistry = $this->createMock(ModRegistry::class);
        /* @var ProcessManager $processManager */
        $processManager = $this->createMock(ProcessManager::class);

        $command = new RenderModIconsCommand($combinationRegistry, $modRegistry, $processManager);

        $this->assertSame($combinationRegistry, $this->extractProperty($command, 'combinationRegistry'));
        $this->assertSame($modRegistry, $this->extractProperty($command, 'modRegistry'));
        $this->assertSame($processManager, $this->extractProperty($command, 'processManager'));
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

        /* @var Console|MockObject $console */
        $console = $this->getMockBuilder(Console::class)
                        ->setMethods(['writeAction'])
                        ->disableOriginalConstructor()
                        ->getMock();
        $console->expects($this->once())
                ->method('writeAction')
                ->with('Rendering 2 icons');

        /* @var RenderModIconsCommand|MockObject $command */
        $command = $this->getMockBuilder(RenderModIconsCommand::class)
                        ->setMethods(['fetchIconHashesOfMod', 'renderIconsWithHashes'])
                        ->disableOriginalConstructor()
                        ->getMock();
        $command->expects($this->once())
                ->method('fetchIconHashesOfMod')
                ->with($mod)
                ->willReturn($iconHashes);
        $command->expects($this->once())
                ->method('renderIconsWithHashes')
                ->with($iconHashes);
        $this->injectProperty($command, 'console', $console);

        /* @var Route $route */
        $route = $this->createMock(Route::class);

        $this->invokeMethod($command, 'processMod', $route, $mod);
    }

    /**
     * Tests the fetchIconHashesOfMod method.
     * @throws ReflectionException
     * @covers ::fetchIconHashesOfMod
     */
    public function testFetchIconHashesOfMod(): void
    {
        $mod = (new Mod())->setCombinationHashes(['abc', 'def']);
        $combination1 = (new Combination())->setIconHashes(['ghi', 'jkl']);
        $combination2 = (new Combination())->setIconHashes(['ghi', 'mno']);
        $expectedResult = ['ghi', 'jkl', 'mno'];

        /* @var RenderModIconsCommand|MockObject $command */
        $command = $this->getMockBuilder(RenderModIconsCommand::class)
                        ->setMethods(['fetchCombination'])
                        ->disableOriginalConstructor()
                        ->getMock();
        $command->expects($this->exactly(2))
                ->method('fetchCombination')
                ->withConsecutive(
                    ['abc'],
                    ['def']
                )
                ->willReturnOnConsecutiveCalls(
                    $combination1,
                    $combination2
                );

        $result = $this->invokeMethod($command, 'fetchIconHashesOfMod', $mod);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Provides the data for the fetchCombination test.
     * @return array
     */
    public function provideFetchCombination(): array
    {
        return [
            [(new Combination())->setName('abc'), false],
            [null, true],
        ];
    }

    /**
     * Tests the fetchCombination method.
     * @param Combination|null $resultGet
     * @param bool $expectException
     * @throws ReflectionException
     * @covers ::fetchCombination
     * @dataProvider provideFetchCombination
     */
    public function testFetchCombination(?Combination $resultGet, bool $expectException): void
    {
        $combinationHash = 'foo';

        /* @var EntityRegistry|MockObject $combinationRegistry */
        $combinationRegistry = $this->getMockBuilder(EntityRegistry::class)
                                    ->setMethods(['get'])
                                    ->disableOriginalConstructor()
                                    ->getMock();
        $combinationRegistry->expects($this->once())
                            ->method('get')
                            ->with($combinationHash)
                            ->willReturn($resultGet);

        if ($expectException) {
            $this->expectException(CommandException::class);
            $this->expectExceptionCode(404);
        }

        /* @var ModRegistry $modRegistry */
        $modRegistry = $this->createMock(ModRegistry::class);
        /* @var ProcessManager $processManager */
        $processManager = $this->createMock(ProcessManager::class);

        /* @var RenderModIconsCommand|MockObject $command */
        $command = $this->getMockBuilder(RenderModIconsCommand::class)
                        ->setConstructorArgs([$combinationRegistry, $modRegistry, $processManager])
                        ->getMockForAbstractClass();

        $result = $this->invokeMethod($command, 'fetchCombination', $combinationHash);
        $this->assertSame($resultGet, $result);
    }

    /**
     * Tests the renderIconsWithHashes method.
     * @throws ReflectionException
     * @covers ::renderIconsWithHashes
     */
    public function testRenderIconsWithHashes(): void
    {
        $iconHashes = ['abc', 'def'];
        /* @var Process $process1 */
        $process1 = $this->createMock(Process::class);
        /* @var Process $process2 */
        $process2 = $this->createMock(Process::class);
        /* @var Console $console */
        $console = $this->createMock(Console::class);

        /* @var ProcessManager|MockObject $processManager */
        $processManager = $this->getMockBuilder(ProcessManager::class)
                               ->setMethods(['addProcess', 'waitForAllProcesses'])
                               ->disableOriginalConstructor()
                               ->getMock();
        $processManager->expects($this->exactly(2))
                       ->method('addProcess')
                       ->withConsecutive(
                           [$process1],
                           [$process2]
                       );
        $processManager->expects($this->once())
                       ->method('waitForAllProcesses');

        /* @var EntityRegistry $combinationRegistry */
        $combinationRegistry = $this->createMock(EntityRegistry::class);
        /* @var ModRegistry $modRegistry */
        $modRegistry = $this->createMock(ModRegistry::class);

        /* @var RenderModIconsCommand|MockObject $command */
        $command = $this->getMockBuilder(RenderModIconsCommand::class)
                        ->setMethods(['createCommandProcess'])
                        ->setConstructorArgs([$combinationRegistry, $modRegistry, $processManager])
                        ->getMock();
        $command->expects($this->exactly(2))
                ->method('createCommandProcess')
                ->withConsecutive(
                    [CommandName::RENDER_ICON, [ParameterName::ICON_HASH => 'abc'], $console],
                    [CommandName::RENDER_ICON, [ParameterName::ICON_HASH => 'def'], $console]
                )
                ->willReturnOnConsecutiveCalls(
                    $process1,
                    $process2
                );
        $this->injectProperty($command, 'console', $console);

        $this->invokeMethod($command, 'renderIconsWithHashes', $iconHashes);
    }
}
