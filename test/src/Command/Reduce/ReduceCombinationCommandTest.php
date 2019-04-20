<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Command\Reduce;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Export\Command\Reduce\ReduceCombinationCommand;
use FactorioItemBrowser\Export\Console\Console;
use FactorioItemBrowser\Export\Reducer\Combination\CombinationReducerManager;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;
use FactorioItemBrowser\ExportData\Registry\EntityRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ZF\Console\Route;

/**
 * The PHPUnit test of the ReduceCombinationCommand class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Command\Reduce\ReduceCombinationCommand
 */
class ReduceCombinationCommandTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        /* @var EntityRegistry $rawCombinationRegistry */
        $rawCombinationRegistry = $this->createMock(EntityRegistry::class);
        /* @var EntityRegistry $reducedCombinationRegistry */
        $reducedCombinationRegistry = $this->createMock(EntityRegistry::class);
        /* @var CombinationReducerManager $reducerManager */
        $reducerManager = $this->createMock(CombinationReducerManager::class);

        $command = new ReduceCombinationCommand($rawCombinationRegistry, $reducedCombinationRegistry, $reducerManager);

        $this->assertSame($rawCombinationRegistry, $this->extractProperty($command, 'combinationRegistry'));
        $this->assertSame($reducedCombinationRegistry, $this->extractProperty($command, 'reducedCombinationRegistry'));
        $this->assertSame($reducerManager, $this->extractProperty($command, 'combinationReducerManager'));
    }

    /**
     * Provides the data for the processCombination test.
     * @return array
     */
    public function provideProcessCombination(): array
    {
        return [
            [false, false, true],
            [true, true, false],
        ];
    }

    /**
     * Tests the processCombination method.
     * @param bool $resultIsEmpty
     * @param bool $expectRemove
     * @param bool $expectSet
     * @throws ReflectionException
     * @covers ::processCombination
     * @dataProvider provideProcessCombination
     */
    public function testProcessCombination(bool $resultIsEmpty, bool $expectRemove, bool $expectSet): void
    {
        $combinationHash = 'abc';
        $reducedCombination = (new Combination())->setName('def');

        /* @var Combination|MockObject $combination */
        $combination = $this->getMockBuilder(Combination::class)
                            ->setMethods(['getName', 'calculateHash'])
                            ->getMock();
        $combination->expects($this->once())
                    ->method('getName')
                    ->willReturn('ghi');
        $combination->expects($expectRemove ? $this->once() : $this->never())
                    ->method('calculateHash')
                    ->willReturn($combinationHash);

        /* @var CombinationReducerManager|MockObject $reducerManager */
        $reducerManager = $this->getMockBuilder(CombinationReducerManager::class)
                               ->setMethods(['reduce'])
                               ->disableOriginalConstructor()
                               ->getMock();
        $reducerManager->expects($this->once())
                       ->method('reduce')
                       ->with($combination)
                       ->willReturn($reducedCombination);

        /* @var EntityRegistry|MockObject $reducedCombinationRegistry */
        $reducedCombinationRegistry = $this->getMockBuilder(EntityRegistry::class)
                                           ->setMethods(['remove', 'set'])
                                           ->disableOriginalConstructor()
                                           ->getMock();
        $reducedCombinationRegistry->expects($expectRemove ? $this->once() : $this->never())
                                   ->method('remove')
                                   ->with($combinationHash);
        $reducedCombinationRegistry->expects($expectSet ? $this->once() : $this->never())
                                   ->method('set')
                                   ->with($reducedCombination);

        /* @var Console|MockObject $console */
        $console = $this->getMockBuilder(Console::class)
                        ->setMethods(['writeAction'])
                        ->disableOriginalConstructor()
                        ->getMock();
        $console->expects($this->once())
                ->method('writeAction')
                ->with('Reducing combination ghi');

        /* @var EntityRegistry $rawCombinationRegistry */
        $rawCombinationRegistry = $this->createMock(EntityRegistry::class);

        /* @var ReduceCombinationCommand|MockObject $command */
        $command = $this->getMockBuilder(ReduceCombinationCommand::class)
                        ->setMethods(['isCombinationEmpty'])
                        ->setConstructorArgs([$rawCombinationRegistry, $reducedCombinationRegistry, $reducerManager])
                        ->getMock();
        $command->expects($this->once())
                ->method('isCombinationEmpty')
                ->with($reducedCombination)
                ->willReturn($resultIsEmpty);
        $this->injectProperty($command, 'console', $console);

        /* @var Route $route */
        $route = $this->createMock(Route::class);

        $this->invokeMethod($command, 'processCombination', $route, $combination);
    }

    /**
     * Provides the data for the isCombinationEmpty test.
     * @return array
     */
    public function provideIsCombinationEmpty(): array
    {
        $combination1 = new Combination();
        $combination1->setLoadedOptionalModNames(['abc']);

        $combination2 = new Combination();
        $combination2->setIconHashes(['def']);

        $combination3 = new Combination();
        $combination3->setItemHashes(['def']);

        $combination4 = new Combination();
        $combination4->setMachineHashes(['def']);

        $combination5 = new Combination();
        $combination5->setRecipeHashes(['def']);

        return [
            [$combination1, true],
            [$combination2, false],
            [$combination3, false],
            [$combination4, false],
            [$combination5, false],
            [new Combination(), false], // Empty instance is actually not considered empty.
        ];
    }

    /**
     * Tests the isCombinationEmpty method.
     * @param Combination $combination
     * @param bool $expectedResult
     * @throws ReflectionException
     * @covers ::isCombinationEmpty
     * @dataProvider provideIsCombinationEmpty
     */
    public function testIsCombinationEmpty(Combination $combination, bool $expectedResult): void
    {
        /* @var EntityRegistry $rawCombinationRegistry */
        $rawCombinationRegistry = $this->createMock(EntityRegistry::class);
        /* @var EntityRegistry $reducedCombinationRegistry */
        $reducedCombinationRegistry = $this->createMock(EntityRegistry::class);
        /* @var CombinationReducerManager $reducerManager */
        $reducerManager = $this->createMock(CombinationReducerManager::class);

        $command = new ReduceCombinationCommand($rawCombinationRegistry, $reducedCombinationRegistry, $reducerManager);

        $result = $this->invokeMethod($command, 'isCombinationEmpty', $combination);
        $this->assertSame($expectedResult, $result);
    }
}
