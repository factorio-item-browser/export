<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Command\Reduce;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Export\Command\Reduce\ReduceModCommand;
use FactorioItemBrowser\Export\Console\Console;
use FactorioItemBrowser\ExportData\Entity\Mod;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;
use FactorioItemBrowser\ExportData\Registry\EntityRegistry;
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
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        /* @var ModRegistry $rawModRegistry */
        $rawModRegistry = $this->createMock(ModRegistry::class);
        /* @var EntityRegistry $reducedCombinationRegistry */
        $reducedCombinationRegistry = $this->createMock(EntityRegistry::class);
        /* @var ModRegistry $reducedModRegistry */
        $reducedModRegistry = $this->createMock(ModRegistry::class);

        $command = new ReduceModCommand($rawModRegistry, $reducedCombinationRegistry, $reducedModRegistry);

        $this->assertSame($rawModRegistry, $this->extractProperty($command, 'modRegistry'));
        $this->assertSame($reducedCombinationRegistry, $this->extractProperty($command, 'reducedCombinationRegistry'));
        $this->assertSame($reducedModRegistry, $this->extractProperty($command, 'reducedModRegistry'));
    }

    /**
     * Tests the processMod method.
     * @throws ReflectionException
     * @covers ::processMod
     */
    public function testProcessMod(): void
    {
        $combinationHashes = ['abc', 'def'];
        $filteredCombinationHashes = ['ghi', 'jkl'];

        $rawMod = new Mod();
        $rawMod->setName('foo')
               ->setCombinationHashes($combinationHashes);
        $expectedReducedMod = new Mod();
        $expectedReducedMod->setName('foo')
                           ->setCombinationHashes($filteredCombinationHashes);

        /* @var ModRegistry|MockObject $reducedModRegistry */
        $reducedModRegistry = $this->getMockBuilder(ModRegistry::class)
                                   ->setMethods(['set', 'saveMods'])
                                   ->disableOriginalConstructor()
                                   ->getMock();
        $reducedModRegistry->expects($this->once())
                           ->method('set')
                           ->with($this->equalTo($expectedReducedMod));
        $reducedModRegistry->expects($this->once())
                           ->method('saveMods');

        /* @var Console|MockObject $console */
        $console = $this->getMockBuilder(Console::class)
                        ->setMethods(['writeAction'])
                        ->disableOriginalConstructor()
                        ->getMock();
        $console->expects($this->once())
                ->method('writeAction')
                ->with('Reducing mod foo');

        /* @var ModRegistry $rawModRegistry */
        $rawModRegistry = $this->createMock(ModRegistry::class);
        /* @var EntityRegistry $reducedCombinationRegistry */
        $reducedCombinationRegistry = $this->createMock(EntityRegistry::class);

        /* @var ReduceModCommand|MockObject $command */
        $command = $this->getMockBuilder(ReduceModCommand::class)
                        ->setMethods(['filterCombinationHashes'])
                        ->setConstructorArgs([$rawModRegistry, $reducedCombinationRegistry, $reducedModRegistry])
                        ->getMock();
        $command->expects($this->once())
                ->method('filterCombinationHashes')
                ->with($combinationHashes)
                ->willReturn($filteredCombinationHashes);
        $this->injectProperty($command, 'console', $console);

        /* @var Route $route */
        $route = $this->createMock(Route::class);

        $this->invokeMethod($command, 'processMod', $route, $rawMod);
    }

    /**
     * Tests the filterCombinationHashes method.
     * @throws ReflectionException
     * @covers ::filterCombinationHashes
     */
    public function testFilterCombinationHashes(): void
    {
        $combinationHashes = ['abc', 'def', 'ghi'];
        $expectedResult = ['abc', 'ghi'];

        $combination1 = (new Combination())->setName('jkl');
        $combination2 = (new Combination())->setName('mno');

        /* @var EntityRegistry|MockObject $reducedCombinationRegistry */
        $reducedCombinationRegistry = $this->getMockBuilder(EntityRegistry::class)
                                           ->setMethods(['get'])
                                           ->disableOriginalConstructor()
                                           ->getMock();
        $reducedCombinationRegistry->expects($this->exactly(3))
                                   ->method('get')
                                   ->withConsecutive(
                                       ['abc'],
                                       ['def'],
                                       ['ghi']
                                   )
                                   ->willReturnOnConsecutiveCalls(
                                       $combination1,
                                       null,
                                       $combination2
                                   );

        /* @var ModRegistry $rawModRegistry */
        $rawModRegistry = $this->createMock(ModRegistry::class);
        /* @var ModRegistry $reducedModRegistry */
        $reducedModRegistry = $this->createMock(ModRegistry::class);

        $command = new ReduceModCommand($rawModRegistry, $reducedCombinationRegistry, $reducedModRegistry);

        $result = $this->invokeMethod($command, 'filterCombinationHashes', $combinationHashes);
        $this->assertEquals($expectedResult, $result);
    }
}
