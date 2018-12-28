<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Command\Export;

use BluePsyduck\Common\Test\ReflectionTrait;
use BluePsyduck\SymfonyProcessManager\ProcessManager;
use FactorioItemBrowser\Export\Combination\CombinationCreator;
use FactorioItemBrowser\Export\Command\Export\ExportModStepCommand;
use FactorioItemBrowser\Export\Console\Console;
use FactorioItemBrowser\Export\Constant\CommandName;
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
 * The PHPUnit test of the ExportModStepCommand class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Command\Export\ExportModStepCommand
 */
class ExportModStepCommandTest extends TestCase
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
        /* @var EntityRegistry $combinationRegistry */
        $combinationRegistry = $this->createMock(EntityRegistry::class);
        /* @var ModRegistry $modRegistry */
        $modRegistry = $this->createMock(ModRegistry::class);
        /* @var ProcessManager $processManager */
        $processManager = $this->createMock(ProcessManager::class);

        $command = new ExportModStepCommand($combinationCreator, $combinationRegistry, $modRegistry, $processManager);

        $this->assertSame($combinationCreator, $this->extractProperty($command, 'combinationCreator'));
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
        $step = 42;
        $combinationHashes = ['abc', 'def'];
        $combinations = [(new Combination())->setName('foo'), (new Combination())->setName('bar')];

        /* @var Mod|MockObject $mod */
        $mod = $this->getMockBuilder(Mod::class)
                    ->setMethods(['getCombinationHashes', 'setCombinationHashes'])
                    ->disableOriginalConstructor()
                    ->getMock();
        $mod->expects($this->once())
            ->method('getCombinationHashes')
            ->willReturn(['abc', 'ghi']);
        $mod->expects($this->once())
            ->method('setCombinationHashes')
            ->with(['abc', 'ghi', 'abc', 'def']);

        /* @var CombinationCreator|MockObject $combinationCreator */
        $combinationCreator = $this->getMockBuilder(CombinationCreator::class)
                                   ->setMethods(['setupForMod'])
                                   ->disableOriginalConstructor()
                                   ->getMock();
        $combinationCreator->expects($this->once())
                           ->method('setupForMod')
                           ->with($mod);

        /* @var ModRegistry|MockObject $modRegistry */
        $modRegistry = $this->getMockBuilder(ModRegistry::class)
                            ->setMethods(['set', 'saveMods'])
                            ->disableOriginalConstructor()
                            ->getMock();
        $modRegistry->expects($this->once())
                    ->method('set')
                    ->with($mod);
        $modRegistry->expects($this->once())
                    ->method('saveMods');

        /* @var Route|MockObject $route */
        $route = $this->getMockBuilder(Route::class)
                      ->setMethods(['getMatchedParam'])
                      ->disableOriginalConstructor()
                      ->getMock();
        $route->expects($this->once())
              ->method('getMatchedParam')
              ->with('step', 0)
              ->willReturn($step);

        /* @var EntityRegistry $combinationRegistry */
        $combinationRegistry = $this->createMock(EntityRegistry::class);
        /* @var ProcessManager $processManager */
        $processManager = $this->createMock(ProcessManager::class);

        /* @var ExportModStepCommand|MockObject $command */
        $command = $this->getMockBuilder(ExportModStepCommand::class)
                        ->setMethods(['fetchCombinations', 'exportCombinations'])
                        ->setConstructorArgs([$combinationCreator, $combinationRegistry, $modRegistry, $processManager])
                        ->getMock();
        $command->expects($this->once())
                ->method('fetchCombinations')
                ->with($step)
                ->willReturn($combinations);
        $command->expects($this->once())
                ->method('exportCombinations')
                ->with($combinations)
                ->willReturn($combinationHashes);

        $this->invokeMethod($command, 'processMod', $route, $mod);
    }

    /**
     * Provides the data for the fetchCombinations test.
     * @return array
     */
    public function provideFetchCombinations(): array
    {
        $combination1 = (new Combination())->setName('abc');
        $combination2 = (new Combination())->setName('def');

        return [
            [0, $combination1, null, [$combination1]],
            [42, null, [$combination1, $combination2], [$combination1, $combination2]],
        ];
    }

    /**
     * Tests the fetchCombinations method.
     * @param int $step
     * @param Combination|null $resultBaseCombination
     * @param array|null $resultCombinations
     * @param array $expectedResult
     * @throws ReflectionException
     * @covers ::fetchCombinations
     * @dataProvider provideFetchCombinations
     */
    public function testFetchCombinations(
        int $step,
        ?Combination $resultBaseCombination,
        ?array $resultCombinations,
        array $expectedResult
    ): void {
        /* @var CombinationCreator|MockObject $combinationCreator */
        $combinationCreator = $this->getMockBuilder(CombinationCreator::class)
                                   ->setMethods(['createBaseCombination', 'createCombinationsWithNumberOfOptionalMods'])
                                   ->disableOriginalConstructor()
                                   ->getMock();
        $combinationCreator->expects($resultBaseCombination === null ? $this->never() : $this->once())
                           ->method('createBaseCombination')
                           ->willReturn($resultBaseCombination);
        $combinationCreator->expects($resultCombinations === null ? $this->never() : $this->once())
                           ->method('createCombinationsWithNumberOfOptionalMods')
                           ->with($step)
                           ->willReturn($resultCombinations);

        /* @var EntityRegistry $combinationRegistry */
        $combinationRegistry = $this->createMock(EntityRegistry::class);
        /* @var ModRegistry $modRegistry */
        $modRegistry = $this->createMock(ModRegistry::class);
        /* @var ProcessManager $processManager */
        $processManager = $this->createMock(ProcessManager::class);

        $command = new ExportModStepCommand($combinationCreator, $combinationRegistry, $modRegistry, $processManager);

        $result = $this->invokeMethod($command, 'fetchCombinations', $step);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the exportCombinations method.
     * @throws ReflectionException
     * @covers ::exportCombinations
     */
    public function testExportCombinations(): void
    {
        $combinations = [(new Combination())->setName('abc'), (new Combination())->setName('def')];
        $combinationHashes = ['ghi', 'jkl'];

        /* @var ExportModStepCommand|MockObject $command */
        $command = $this->getMockBuilder(ExportModStepCommand::class)
                        ->setMethods(['persistCombinations', 'runCombinationsCommand'])
                        ->disableOriginalConstructor()
                        ->getMock();
        $command->expects($this->once())
                ->method('persistCombinations')
                ->with($combinations)
                ->willReturn($combinationHashes);
        $command->expects($this->exactly(2))
                ->method('runCombinationsCommand')
                ->withConsecutive(
                    [CommandName::EXPORT_COMBINATION, $combinationHashes],
                    [CommandName::REDUCE_COMBINATION, $combinationHashes]
                );

        $result = $this->invokeMethod($command, 'exportCombinations', $combinations);

        $this->assertSame($combinationHashes, $result);
    }

    /**
     * Tests the persistCombinations method.
     * @throws ReflectionException
     * @covers ::persistCombinations
     */
    public function testPersistCombinations(): void
    {
        $combination1 = (new Combination())->setName('abc');
        $combination2 = (new Combination())->setName('def');
        $expectedResult = ['ghi', 'jkl'];

        /* @var EntityRegistry|MockObject $combinationRegistry */
        $combinationRegistry = $this->getMockBuilder(EntityRegistry::class)
                                    ->setMethods(['set'])
                                    ->disableOriginalConstructor()
                                    ->getMock();
        $combinationRegistry->expects($this->exactly(2))
                            ->method('set')
                            ->withConsecutive(
                                [$combination1],
                                [$combination2]
                            )
                            ->willReturnOnConsecutiveCalls(
                                'ghi',
                                'jkl'
                            );

        /* @var CombinationCreator $combinationCreator */
        $combinationCreator = $this->createMock(CombinationCreator::class);
        /* @var ModRegistry $modRegistry */
        $modRegistry = $this->createMock(ModRegistry::class);
        /* @var ProcessManager $processManager */
        $processManager = $this->createMock(ProcessManager::class);

        $command = new ExportModStepCommand($combinationCreator, $combinationRegistry, $modRegistry, $processManager);

        $result = $this->invokeMethod($command, 'persistCombinations', [$combination1, $combination2]);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the runCombinationsCommand method.
     * @throws ReflectionException
     * @covers ::runCombinationsCommand
     */
    public function testRunCombinationsCommand(): void
    {
        $commandName = 'abc';
        $combinationHashes = ['def', 'ghi'];
        /* @var Process $process1 */
        $process1 = $this->createMock(Process::class);
        /* @var Process $process2 */
        $process2 = $this->createMock(Process::class);

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

        /* @var Console $console */
        $console = $this->createMock(Console::class);
        /* @var CombinationCreator $combinationCreator */
        $combinationCreator = $this->createMock(CombinationCreator::class);
        /* @var EntityRegistry $combinationRegistry */
        $combinationRegistry = $this->createMock(EntityRegistry::class);
        /* @var ModRegistry $modRegistry */
        $modRegistry = $this->createMock(ModRegistry::class);

        /* @var ExportModStepCommand|MockObject $command */
        $command = $this->getMockBuilder(ExportModStepCommand::class)
                        ->setMethods(['createCommandProcess'])
                        ->setConstructorArgs([$combinationCreator, $combinationRegistry, $modRegistry, $processManager])
                        ->getMock();
        $command->expects($this->exactly(2))
                ->method('createCommandProcess')
                ->withConsecutive(
                    [$commandName, ['combinationHash' => 'def'], $console],
                    [$commandName, ['combinationHash' => 'ghi'], $console]
                )
                ->willReturnOnConsecutiveCalls(
                    $process1,
                    $process2
                );
        $this->injectProperty($command, 'console', $console);

        $this->invokeMethod($command, 'runCombinationsCommand', $commandName, $combinationHashes);
    }
}
