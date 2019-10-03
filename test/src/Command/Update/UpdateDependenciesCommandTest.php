<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Command\Update;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Export\Command\Update\UpdateDependenciesCommand;
use FactorioItemBrowser\Export\Constant\ParameterName;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Mod\DependencyReader;
use FactorioItemBrowser\ExportData\Entity\Mod;
use FactorioItemBrowser\ExportData\Entity\Mod\Dependency;
use FactorioItemBrowser\ExportData\Registry\ModRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Zend\Console\Adapter\AdapterInterface;
use ZF\Console\Route;

/**
 * The PHPUnit test of the UpdateDependenciesCommand class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Command\Update\UpdateDependenciesCommand
 */
class UpdateDependenciesCommandTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests the constructing.
     * @covers ::__construct
     * @throws ReflectionException
     */
    public function testConstruct(): void
    {
        /* @var DependencyReader $dependencyReader */
        $dependencyReader = $this->createMock(DependencyReader::class);
        /* @var ModRegistry $modRegistry */
        $modRegistry = $this->createMock(ModRegistry::class);

        $command = new UpdateDependenciesCommand($dependencyReader, $modRegistry);
        $this->assertSame($dependencyReader, $this->extractProperty($command, 'dependencyReader'));
        $this->assertSame($modRegistry, $this->extractProperty($command, 'modRegistry'));
    }

    /**
     * Tests the execute() method.
     * @throws ReflectionException
     * @covers ::execute
     */
    public function testExecute(): void
    {
        /* @var Route $route */
        $route = $this->createMock(Route::class);
        /* @var DependencyReader $dependencyReader */
        $dependencyReader = $this->createMock(DependencyReader::class);

        /* @var AdapterInterface|MockObject $console */
        $console = $this->getMockBuilder(AdapterInterface::class)
                        ->setMethods(['writeLine'])
                        ->getMockForAbstractClass();
        $console->expects($this->once())
                ->method('writeLine')
                ->with('Updating dependencies...');

        /* @var ModRegistry|MockObject $modRegistry */
        $modRegistry = $this->getMockBuilder(ModRegistry::class)
                            ->setMethods(['saveMods'])
                            ->disableOriginalConstructor()
                            ->getMock();
        $modRegistry->expects($this->once())
                    ->method('saveMods');

        /* @var UpdateDependenciesCommand|MockObject $command */
        $command = $this->getMockBuilder(UpdateDependenciesCommand::class)
                        ->setMethods(['getModNames', 'updateDependenciesOfMod'])
                        ->setConstructorArgs([$dependencyReader, $modRegistry])
                        ->getMock();
        $command->expects($this->once())
                ->method('getModNames')
                ->with($route)
                ->willReturn(['abc', 'def']);
        $command->expects($this->exactly(2))
                ->method('updateDependenciesOfMod')
                ->withConsecutive(
                    ['abc'],
                    ['def']
                );

        $this->injectProperty($command, 'console', $console);
        $this->invokeMethod($command, 'execute', $route);
    }

    /**
     * Provides the data for the getModNames test.
     * @return array
     */
    public function provideGetModNames(): array
    {
        return [
            ['', ['abc', 'def'], ['abc', 'def']],
            ['abc', null, ['abc']],
        ];
    }

    /**
     * Tests the getModNames method.
     * @param string $resultGetMatchedParam
     * @param array|null $resultGetAllNames
     * @param array $expectedResult
     * @throws ReflectionException
     * @covers ::getModNames
     * @dataProvider provideGetModNames
     */
    public function testGetModNames(
        string $resultGetMatchedParam,
        ?array $resultGetAllNames,
        array $expectedResult
    ): void {
        /* @var DependencyReader $dependencyReader */
        $dependencyReader = $this->createMock(DependencyReader::class);

        /* @var Route|MockObject $route */
        $route = $this->getMockBuilder(Route::class)
                      ->setMethods(['getMatchedParam'])
                      ->disableOriginalConstructor()
                      ->getMock();
        $route->expects($this->once())
              ->method('getMatchedParam')
              ->with(ParameterName::MOD_NAME, '')
              ->willReturn($resultGetMatchedParam);

        /* @var ModRegistry|MockObject $modRegistry */
        $modRegistry = $this->getMockBuilder(ModRegistry::class)
                            ->setMethods(['getAllNames'])
                            ->disableOriginalConstructor()
                            ->getMock();
        $modRegistry->expects($resultGetAllNames === null ? $this->never() : $this->once())
                    ->method('getAllNames')
                    ->willReturn($resultGetAllNames === null ? [] : $resultGetAllNames);

        $command = new UpdateDependenciesCommand($dependencyReader, $modRegistry);
        $result = $this->invokeMethod($command, 'getModNames', $route);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Provides the data for the updateDependenciesOfMod test.
     * @return array
     */
    public function provideUpdateDependenciesOfMod(): array
    {
        return [
            [true, false, true],
            [false, true, false],
        ];
    }

    /**
     * Tests the updateDependenciesOfMod method.
     * @param bool $withMod
     * @param bool $expectException
     * @param bool $expectRead
     * @throws ReflectionException
     * @covers ::updateDependenciesOfMod
     * @dataProvider provideUpdateDependenciesOfMod
     */
    public function testUpdateDependenciesOfMod(bool $withMod, bool $expectException, bool $expectRead): void
    {
        $modName = '';
        $dependencies = [new Dependency(), new Dependency()];

        /* @var Mod|MockObject $mod */
        $mod = $this->getMockBuilder(Mod::class)
                    ->setMethods(['setDependencies'])
                    ->disableOriginalConstructor()
                    ->getMock();
        $mod->expects($expectRead ? $this->once() : $this->never())
            ->method('setDependencies')
            ->with($dependencies);

        /* @var ModRegistry|MockObject $modRegistry */
        $modRegistry = $this->getMockBuilder(ModRegistry::class)
                            ->setMethods(['get', 'set'])
                            ->disableOriginalConstructor()
                            ->getMock();
        $modRegistry->expects($this->once())
                    ->method('get')
                    ->with($modName)
                    ->willReturn($withMod ? $mod : null);
        $modRegistry->expects($expectRead ? $this->once() : $this->never())
                    ->method('set')
                    ->with($mod);

        /* @var DependencyReader|MockObject $dependencyReader */
        $dependencyReader = $this->getMockBuilder(DependencyReader::class)
                                 ->setMethods(['read'])
                                 ->disableOriginalConstructor()
                                 ->getMock();
        $dependencyReader->expects($expectRead ? $this->once() : $this->never())
                         ->method('read')
                         ->with($mod)
                         ->willReturn($dependencies);

        if ($expectException) {
            $this->expectException(ExportException::class);
        }

        $command = new UpdateDependenciesCommand($dependencyReader, $modRegistry);
        $this->invokeMethod($command, 'updateDependenciesOfMod', $modName);
    }
}
