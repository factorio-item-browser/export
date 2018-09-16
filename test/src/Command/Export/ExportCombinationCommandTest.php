<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Command\Export;

use BluePsyduck\Common\Data\DataContainer;
use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Export\Command\Export\ExportCombinationCommand;
use FactorioItemBrowser\Export\Exception\CommandException;
use FactorioItemBrowser\Export\Factorio\Instance;
use FactorioItemBrowser\Export\Parser\ParserManager;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;
use FactorioItemBrowser\ExportData\Registry\EntityRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Zend\Console\Adapter\AdapterInterface;
use ZF\Console\Route;

/**
 * The PHPUnit test of the ExportCombinationCommand class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Command\Export\ExportCombinationCommand
 */
class ExportCombinationCommandTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests the constructing.
     * @covers ::__construct
     * @throws ReflectionException
     */
    public function testConstruct(): void
    {
        /* @var EntityRegistry $combinationRegistry */
        $combinationRegistry = $this->createMock(EntityRegistry::class);
        /* @var Instance $instance */
        $instance = $this->createMock(Instance::class);
        /* @var ParserManager $parserManager */
        $parserManager = $this->createMock(ParserManager::class);

        $command = new ExportCombinationCommand($combinationRegistry, $instance, $parserManager);
        $this->assertSame($combinationRegistry, $this->extractProperty($command, 'combinationRegistry'));
        $this->assertSame($instance, $this->extractProperty($command, 'instance'));
        $this->assertSame($parserManager, $this->extractProperty($command, 'parserManager'));
    }

    /**
     * Provides the data for the execute test.
     * @return array
     */
    public function provideExecute(): array
    {
        return [
            [true, true, false],
            [false, false, true],
        ];
    }

    /**
     * Tests the execute method.
     * @param bool $withCombination
     * @param bool $expectParse
     * @param bool $expectException
     * @throws ReflectionException
     * @covers ::execute
     * @dataProvider provideExecute
     */
    public function testExecute(bool $withCombination, bool $expectParse, bool $expectException): void
    {
        $combinationHash = 'abc';
        $combination = null;
        if ($withCombination) {
            $combination = (new Combination())->setName('def');
        }
        $dumpData = new DataContainer(['ghi' => 'jkl']);

        /* @var Route|MockObject $route */
        $route = $this->getMockBuilder(Route::class)
                      ->setMethods(['getMatchedParam'])
                      ->disableOriginalConstructor()
                      ->getMock();
        $route->expects($this->once())
              ->method('getMatchedParam')
              ->with('combinationHash', '')
              ->willReturn($combinationHash);

        /* @var EntityRegistry|MockObject $combinationRegistry */
        $combinationRegistry = $this->getMockBuilder(EntityRegistry::class)
                                    ->setMethods(['get', 'set'])
                                    ->disableOriginalConstructor()
                                    ->getMock();
        $combinationRegistry->expects($this->once())
                            ->method('get')
                            ->with($combinationHash)
                            ->willReturn($combination);
        $combinationRegistry->expects($expectParse ? $this->once() : $this->never())
                            ->method('set')
                            ->with($combination);

        /* @var AdapterInterface|MockObject $console */
        $console = $this->getMockBuilder(AdapterInterface::class)
                        ->setMethods(['writeLine'])
                        ->getMockForAbstractClass();
        $console->expects($expectParse ? $this->once() : $this->never())
                ->method('writeLine')
                ->with('Exporting combination def...');

        /* @var Instance|MockObject $instance */
        $instance = $this->getMockBuilder(Instance::class)
                         ->setMethods(['run'])
                         ->disableOriginalConstructor()
                         ->getMock();
        $instance->expects($expectParse ? $this->once() : $this->never())
                 ->method('run')
                 ->with($combination)
                 ->willReturn($dumpData);

        /* @var ParserManager|MockObject $parserManager */
        $parserManager = $this->getMockBuilder(ParserManager::class)
                              ->setMethods(['parse'])
                              ->disableOriginalConstructor()
                              ->getMock();
        $parserManager->expects($expectParse ? $this->once() : $this->never())
                      ->method('parse')
                      ->with($combination, $dumpData);

        if ($expectException) {
            $this->expectException(CommandException::class);
        }

        $command = new ExportCombinationCommand($combinationRegistry, $instance, $parserManager);
        $this->injectProperty($command, 'console', $console);
        $this->invokeMethod($command, 'execute', $route);
    }
}
