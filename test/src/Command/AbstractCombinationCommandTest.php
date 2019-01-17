<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Command;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Export\Command\AbstractCombinationCommand;
use FactorioItemBrowser\Export\Constant\ParameterName;
use FactorioItemBrowser\Export\Exception\CommandException;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;
use FactorioItemBrowser\ExportData\Registry\EntityRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ZF\Console\Route;

/**
 * The PHPUnit test of the AbstractProcessCombinationCommand class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Command\AbstractCombinationCommand
 */
class AbstractCombinationCommandTest extends TestCase
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

        /* @var AbstractCombinationCommand|MockObject $command */
        $command = $this->getMockBuilder(AbstractCombinationCommand::class)
                        ->setConstructorArgs([$combinationRegistry])
                        ->getMockForAbstractClass();

        $this->assertSame($combinationRegistry, $this->extractProperty($command, 'combinationRegistry'));
    }

    /**
     * Tests the execute method.
     * @throws ReflectionException
     * @covers ::execute
     */
    public function testExecute(): void
    {
        $combinationHash = 'abc';
        $combination = (new Combination())->setName('def');

        /* @var Route|MockObject $route */
        $route = $this->getMockBuilder(Route::class)
                      ->setMethods(['getMatchedParam'])
                      ->disableOriginalConstructor()
                      ->getMock();
        $route->expects($this->once())
              ->method('getMatchedParam')
              ->with(ParameterName::COMBINATION_HASH, '')
              ->willReturn($combinationHash);

        /* @var AbstractCombinationCommand|MockObject $command */
        $command = $this->getMockBuilder(AbstractCombinationCommand::class)
                        ->setMethods(['fetchCombination', 'processCombination'])
                        ->disableOriginalConstructor()
                        ->getMockForAbstractClass();
        $command->expects($this->once())
                ->method('fetchCombination')
                ->with($combinationHash)
                ->willReturn($combination);
        $command->expects($this->once())
                ->method('processCombination')
                ->with($route, $combination);

        $this->invokeMethod($command, 'execute', $route);
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

        /* @var AbstractCombinationCommand|MockObject $command */
        $command = $this->getMockBuilder(AbstractCombinationCommand::class)
                        ->setConstructorArgs([$combinationRegistry])
                        ->getMockForAbstractClass();

        $result = $this->invokeMethod($command, 'fetchCombination', $combinationHash);
        $this->assertSame($resultGet, $result);
    }
}
