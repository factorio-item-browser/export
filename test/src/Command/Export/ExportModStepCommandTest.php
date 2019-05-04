<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Command\Export;

use BluePsyduck\Common\Test\ReflectionTrait;
use BluePsyduck\SymfonyProcessManager\ProcessManager;
use FactorioItemBrowser\Export\Combination\CombinationCreator;
use FactorioItemBrowser\Export\Command\Export\ExportModStepCommand;
use FactorioItemBrowser\Export\Console\Console;
use FactorioItemBrowser\Export\Constant\CommandName;
use FactorioItemBrowser\Export\Constant\ParameterName;
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
     * The mocked combination creator.
     * @var CombinationCreator&MockObject
     */
    protected $combinationCreator;

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

        $this->combinationCreator = $this->createMock(CombinationCreator::class);
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
        $command = new ExportModStepCommand(
            $this->combinationCreator,
            $this->combinationRegistry,
            $this->modRegistry,
            $this->processManager
        );

        $this->assertSame($this->combinationCreator, $this->extractProperty($command, 'combinationCreator'));
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
        $step = 42;
        $combinationHashes = ['abc', 'def'];
        $combinations = [
            $this->createMock(Combination::class),
            $this->createMock(Combination::class),
        ];

        /* @var Mod&MockObject $mod */
        $mod = $this->createMock(Mod::class);
        $mod->expects($this->once())
            ->method('getCombinationHashes')
            ->willReturn(['abc', 'ghi']);
        $mod->expects($this->once())
            ->method('setCombinationHashes')
            ->with($this->equalTo(['abc', 'ghi', 'abc', 'def']));

        /* @var Route&MockObject $route */
        $route = $this->createMock(Route::class);
        $route->expects($this->once())
              ->method('getMatchedParam')
              ->with($this->identicalTo(ParameterName::STEP), $this->identicalTo(0))
              ->willReturn($step);

        $this->combinationCreator->expects($this->once())
                                 ->method('setupForMod')
                                 ->with($this->identicalTo($mod));

        /* @var ExportModStepCommand|MockObject $command */
        $command = $this->getMockBuilder(ExportModStepCommand::class)
                        ->setMethods(['fetchCombinations', 'exportCombinations', 'persistMod'])
                        ->setConstructorArgs([
                            $this->combinationCreator,
                            $this->combinationRegistry,
                            $this->modRegistry,
                            $this->processManager,
                        ])
                        ->getMock();
        $command->expects($this->once())
                ->method('fetchCombinations')
                ->with($this->identicalTo($step))
                ->willReturn($combinations);
        $command->expects($this->once())
                ->method('exportCombinations')
                ->with($this->identicalTo($combinations))
                ->willReturn($combinationHashes);
        $command->expects($this->once())
                ->method('persistMod')
                ->with($this->identicalTo($mod));

        $this->invokeMethod($command, 'processMod', $route, $mod);
    }

    /**
     * Tests the fetchCombinations method.
     * @throws ReflectionException
     * @covers ::fetchCombinations
     */
    public function testFetchCombinations(): void
    {
        $step = 42;

        $combinations = [
            $this->createMock(Combination::class),
            $this->createMock(Combination::class),
        ];

        $this->combinationCreator->expects($this->once())
                                 ->method('createCombinationsWithNumberOfOptionalMods')
                                 ->with($this->identicalTo($step))
                                 ->willReturn($combinations);
        $this->combinationCreator->expects($this->never())
                                 ->method('createBaseCombination');

        $command = new ExportModStepCommand(
            $this->combinationCreator,
            $this->combinationRegistry,
            $this->modRegistry,
            $this->processManager
        );
        $result = $this->invokeMethod($command, 'fetchCombinations', $step);

        $this->assertSame($combinations, $result);
    }

    /**
     * Tests the fetchCombinations method.
     * @throws ReflectionException
     * @covers ::fetchCombinations
     */
    public function testFetchCombinationsWithStep0(): void
    {
        $step = 0;

        /* @var Combination&MockObject $baseCombination */
        $baseCombination = $this->createMock(Combination::class);

        $expectedResult = [$baseCombination];

        $this->combinationCreator->expects($this->once())
                                 ->method('createBaseCombination')
                                 ->willReturn($baseCombination);
        $this->combinationCreator->expects($this->never())
                                 ->method('createCombinationsWithNumberOfOptionalMods');

        $command = new ExportModStepCommand(
            $this->combinationCreator,
            $this->combinationRegistry,
            $this->modRegistry,
            $this->processManager
        );
        $result = $this->invokeMethod($command, 'fetchCombinations', $step);

        $this->assertSame($expectedResult, $result);
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
        /* @var Combination&MockObject $combination1 */
        $combination1 = $this->createMock(Combination::class);
        /* @var Combination&MockObject $combination2 */
        $combination2 = $this->createMock(Combination::class);

        $expectedResult = ['ghi', 'jkl'];

        $this->combinationRegistry->expects($this->exactly(2))
                                  ->method('set')
                                  ->withConsecutive(
                                      [$this->identicalTo($combination1)],
                                      [$this->identicalTo($combination2)]
                                  )
                                  ->willReturnOnConsecutiveCalls(
                                      'ghi',
                                      'jkl'
                                  );

        $command = new ExportModStepCommand(
            $this->combinationCreator,
            $this->combinationRegistry,
            $this->modRegistry,
            $this->processManager
        );
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
        /* @var Console $console */
        $console = $this->createMock(Console::class);

        $this->processManager->expects($this->exactly(2))
                             ->method('addProcess')
                             ->withConsecutive(
                                 [$this->identicalTo($process1)],
                                 [$this->identicalTo($process2)]
                             );
        $this->processManager->expects($this->once())
                             ->method('waitForAllProcesses');

        /* @var ExportModStepCommand|MockObject $command */
        $command = $this->getMockBuilder(ExportModStepCommand::class)
                        ->setMethods(['createCommandProcess'])
                        ->setConstructorArgs([
                            $this->combinationCreator,
                            $this->combinationRegistry,
                            $this->modRegistry,
                            $this->processManager,
                        ])
                        ->getMock();
        $command->expects($this->exactly(2))
                ->method('createCommandProcess')
                ->withConsecutive(
                    [
                        $this->identicalTo($commandName),
                        $this->identicalTo([ParameterName::COMBINATION_HASH => 'def']),
                        $this->identicalTo($console)
                    ],
                    [
                        $this->identicalTo($commandName),
                        $this->identicalTo([ParameterName::COMBINATION_HASH => 'ghi']),
                        $this->identicalTo($console)
                    ]
                )
                ->willReturnOnConsecutiveCalls(
                    $process1,
                    $process2
                );
        $this->injectProperty($command, 'console', $console);

        $this->invokeMethod($command, 'runCombinationsCommand', $commandName, $combinationHashes);
    }
}
