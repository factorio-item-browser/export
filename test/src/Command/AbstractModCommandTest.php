<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Command;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Export\Command\AbstractModCommand;
use FactorioItemBrowser\Export\Exception\CommandException;
use FactorioItemBrowser\ExportData\Entity\Mod;
use FactorioItemBrowser\ExportData\Registry\ModRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ZF\Console\Route;

/**
 * The PHPUnit test of the AbstractProcessModCommand class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Command\AbstractModCommand
 */
class AbstractModCommandTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        /* @var ModRegistry $modRegistry */
        $modRegistry = $this->createMock(ModRegistry::class);

        /* @var AbstractModCommand|MockObject $command */
        $command = $this->getMockBuilder(AbstractModCommand::class)
                        ->setConstructorArgs([$modRegistry])
                        ->getMockForAbstractClass();

        $this->assertSame($modRegistry, $this->extractProperty($command, 'modRegistry'));
    }

    /**
     * Tests the execute method.
     * @throws ReflectionException
     * @covers ::execute
     */
    public function testExecute(): void
    {
        $modName = 'abc';
        $mod = (new Mod())->setName('def');

        /* @var Route|MockObject $route */
        $route = $this->getMockBuilder(Route::class)
                      ->setMethods(['getMatchedParam'])
                      ->disableOriginalConstructor()
                      ->getMock();
        $route->expects($this->once())
              ->method('getMatchedParam')
              ->with('modName', '')
              ->willReturn($modName);

        /* @var AbstractModCommand|MockObject $command */
        $command = $this->getMockBuilder(AbstractModCommand::class)
                        ->setMethods(['fetchMod', 'processMod'])
                        ->disableOriginalConstructor()
                        ->getMockForAbstractClass();
        $command->expects($this->once())
                ->method('fetchMod')
                ->with($modName)
                ->willReturn($mod);
        $command->expects($this->once())
                ->method('processMod')
                ->with($route, $mod);

        $this->invokeMethod($command, 'execute', $route);
    }

    /**
     * Provides the data for the fetchMod test.
     * @return array
     */
    public function provideFetchMod(): array
    {
        return [
            [(new Mod())->setName('abc'), false],
            [null, true],
        ];
    }

    /**
     * Tests the fetchMod method.
     * @param Mod|null $resultGet
     * @param bool $expectException
     * @throws ReflectionException
     * @covers ::fetchMod
     * @dataProvider provideFetchMod
     */
    public function testFetchMod(?Mod $resultGet, bool $expectException): void
    {
        $modName = 'foo';

        /* @var ModRegistry|MockObject $modRegistry */
        $modRegistry = $this->getMockBuilder(ModRegistry::class)
                            ->setMethods(['get'])
                            ->disableOriginalConstructor()
                            ->getMock();
        $modRegistry->expects($this->once())
                    ->method('get')
                    ->with($modName)
                    ->willReturn($resultGet);

        if ($expectException) {
            $this->expectException(CommandException::class);
            $this->expectExceptionCode(404);
        }

        /* @var AbstractModCommand|MockObject $command */
        $command = $this->getMockBuilder(AbstractModCommand::class)
                        ->setConstructorArgs([$modRegistry])
                        ->getMockForAbstractClass();

        $result = $this->invokeMethod($command, 'fetchMod', $modName);
        $this->assertSame($resultGet, $result);
    }
}
