<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Command\Export;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Export\Command\Export\ExportModWithDependenciesCommand;
use FactorioItemBrowser\Export\Console\Console;
use FactorioItemBrowser\Export\Constant\CommandName;
use FactorioItemBrowser\Export\Constant\ParameterName;
use FactorioItemBrowser\Export\Mod\DependencyResolver;
use FactorioItemBrowser\ExportData\Registry\ModRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ZF\Console\Route;

/**
 * The PHPUnit test of the ExportModWithDependenciesCommand class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Command\Export\ExportModWithDependenciesCommand
 */
class ExportModWithDependenciesCommandTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        /* @var DependencyResolver $dependencyResolver */
        $dependencyResolver = $this->createMock(DependencyResolver::class);
        /* @var ModRegistry $modRegistry */
        $modRegistry = $this->createMock(ModRegistry::class);

        $command = new ExportModWithDependenciesCommand($dependencyResolver, $modRegistry);

        $this->assertSame($dependencyResolver, $this->extractProperty($command, 'dependencyResolver'));
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
        $modNamesToExport = ['def', 'ghi'];
        $sortedModNamesToExport = ['jkl', 'mno'];

        /* @var Route|MockObject $route */
        $route = $this->getMockBuilder(Route::class)
                      ->setMethods(['getMatchedParam'])
                      ->disableOriginalConstructor()
                      ->getMock();
        $route->expects($this->once())
              ->method('getMatchedParam')
              ->with(ParameterName::MOD_NAME, '')
              ->willReturn($modName);

        /* @var Console|MockObject $console */
        $console = $this->getMockBuilder(Console::class)
                        ->setMethods(['writeAction'])
                        ->disableOriginalConstructor()
                        ->getMock();
        $console->expects($this->once())
                ->method('writeAction')
                ->with('Exporting 2 mods');

        /* @var ExportModWithDependenciesCommand|MockObject $command */
        $command = $this->getMockBuilder(ExportModWithDependenciesCommand::class)
                        ->setMethods(['getModNamesToExport', 'sortModNames', 'runSubCommands'])
                        ->disableOriginalConstructor()
                        ->getMock();
        $command->expects($this->once())
                ->method('getModNamesToExport')
                ->with($modName)
                ->willReturn($modNamesToExport);
        $command->expects($this->once())
                ->method('sortModNames')
                ->with($modNamesToExport)
                ->willReturn($sortedModNamesToExport);
        $command->expects($this->once())
                ->method('runSubCommands')
                ->with($sortedModNamesToExport);
        $this->injectProperty($command, 'console', $console);

        $this->invokeMethod($command, 'execute', $route);
    }

    /**
     * Tests the getModNamesToExport method.
     * @throws ReflectionException
     * @covers ::getModNamesToExport
     */
    public function testGetModNamesToExport(): void
    {
        $baseModName = 'abc';
        $allModNames = ['def', 'abc', 'ghi'];
        $expectedResult = ['def', 'abc'];

        /* @var ModRegistry|MockObject $modRegistry */
        $modRegistry = $this->getMockBuilder(ModRegistry::class)
                            ->setMethods(['getAllNames'])
                            ->disableOriginalConstructor()
                            ->getMock();
        $modRegistry->expects($this->once())
                    ->method('getAllNames')
                    ->willReturn($allModNames);

        /* @var DependencyResolver $dependencyResolver */
        $dependencyResolver = $this->createMock(DependencyResolver::class);

        /* @var ExportModWithDependenciesCommand|MockObject $command */
        $command = $this->getMockBuilder(ExportModWithDependenciesCommand::class)
                        ->setMethods(['hasDependency'])
                        ->setConstructorArgs([$dependencyResolver, $modRegistry])
                        ->getMock();
        $command->expects($this->exactly(2))
                ->method('hasDependency')
                ->withConsecutive(
                    ['abc', 'def'],
                    ['abc', 'ghi']
                )
                ->willReturnOnConsecutiveCalls(
                    true,
                    false
                );

        $result = $this->invokeMethod($command, 'getModNamesToExport', $baseModName);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Provides the data for the hasDependency test.
     * @return array
     */
    public function provideHasDependency(): array
    {
        return [
            ['abc', 'def', ['abc', 'ghi'], null, true],
            ['abc', 'def', ['ghi'], ['abc', 'jkl'], true],
            ['abc', 'def', ['ghi'], ['jkl'], false],
        ];
    }

    /**
     * Tests the hasDependency method.
     * @param string $requiredModName
     * @param string $modNameToCheck
     * @param array $resultMandatory
     * @param array|null $resultOptional
     * @param bool $expectedResult
     * @throws ReflectionException
     * @covers ::hasDependency
     * @dataProvider provideHasDependency
     */
    public function testHasDependency(
        string $requiredModName,
        string $modNameToCheck,
        array $resultMandatory,
        ?array $resultOptional,
        bool $expectedResult
    ): void {
        /* @var DependencyResolver|MockObject $dependencyResolver */
        $dependencyResolver = $this->getMockBuilder(DependencyResolver::class)
                                   ->setMethods(['resolveMandatoryDependencies', 'resolveOptionalDependencies'])
                                   ->disableOriginalConstructor()
                                   ->getMock();
        $dependencyResolver->expects($this->once())
                           ->method('resolveMandatoryDependencies')
                           ->with([$modNameToCheck])
                           ->willReturn($resultMandatory);
        $dependencyResolver->expects($resultOptional === null ? $this->never() : $this->once())
                           ->method('resolveOptionalDependencies')
                           ->with([$modNameToCheck], $resultMandatory)
                           ->willReturn($resultOptional);

        /* @var ModRegistry $modRegistry */
        $modRegistry = $this->createMock(ModRegistry::class);

        $command = new ExportModWithDependenciesCommand($dependencyResolver, $modRegistry);
        $result = $this->invokeMethod($command, 'hasDependency', $requiredModName, $modNameToCheck);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the sortModNames method.
     * @throws ReflectionException
     * @covers ::sortModNames
     */
    public function testSortModNames(): void
    {
        $modNames = ['abc', 'def'];
        $resultDependencies = ['def', 'ghi', 'abc'];
        $expectedResult = ['def', 'abc'];

        /* @var DependencyResolver|MockObject $dependencyResolver */
        $dependencyResolver = $this->getMockBuilder(DependencyResolver::class)
                                   ->setMethods(['resolveMandatoryDependencies'])
                                   ->disableOriginalConstructor()
                                   ->getMock();
        $dependencyResolver->expects($this->once())
                           ->method('resolveMandatoryDependencies')
                           ->with($modNames)
                           ->willReturn($resultDependencies);

        /* @var ModRegistry $modRegistry */
        $modRegistry = $this->createMock(ModRegistry::class);

        $command = new ExportModWithDependenciesCommand($dependencyResolver, $modRegistry);
        $result = $this->invokeMethod($command, 'sortModNames', $modNames);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the runSubCommands method.
     * @throws ReflectionException
     * @covers ::runSubCommands
     */
    public function testRunSubCommands(): void
    {
        $modNames = ['abc', 'def'];

        /* @var Console $console */
        $console = $this->createMock(Console::class);

        /* @var ExportModWithDependenciesCommand|MockObject $command */
        $command = $this->getMockBuilder(ExportModWithDependenciesCommand::class)
                        ->setMethods(['runCommand'])
                        ->disableOriginalConstructor()
                        ->getMock();
        $command->expects($this->exactly(2))
                ->method('runCommand')
                ->withConsecutive(
                    [CommandName::EXPORT_MOD, [ParameterName::MOD_NAME => 'abc'], $console],
                    [CommandName::EXPORT_MOD, [ParameterName::MOD_NAME => 'def'], $console]
                );
        $this->injectProperty($command, 'console', $console);

        $this->invokeMethod($command, 'runSubCommands', $modNames);
    }
}
