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
     * The mocked dependency resolver.
     * @var DependencyResolver&MockObject
     */
    protected $dependencyResolver;

    /**
     * The mocked mod registry.
     * @var ModRegistry&MockObject
     */
    protected $modRegistry;

    /**
     * Sets up the test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->dependencyResolver = $this->createMock(DependencyResolver::class);
        $this->modRegistry = $this->createMock(ModRegistry::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $command = new ExportModWithDependenciesCommand($this->dependencyResolver, $this->modRegistry);

        $this->assertSame($this->dependencyResolver, $this->extractProperty($command, 'dependencyResolver'));
        $this->assertSame($this->modRegistry, $this->extractProperty($command, 'modRegistry'));
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

        /* @var Route&MockObject $route */
        $route = $this->createMock(Route::class);
        $route->expects($this->once())
              ->method('getMatchedParam')
              ->with(ParameterName::MOD_NAME, '')
              ->willReturn($modName);

        /* @var Console&MockObject $console */
        $console = $this->createMock(Console::class);
        $console->expects($this->once())
                ->method('writeAction')
                ->with('Exporting 2 mods');

        /* @var ExportModWithDependenciesCommand|MockObject $command */
        $command = $this->getMockBuilder(ExportModWithDependenciesCommand::class)
                        ->setMethods(['getModNamesToExport', 'sortModNames', 'runSubCommands'])
                        ->setConstructorArgs([$this->dependencyResolver, $this->modRegistry])
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

        $this->modRegistry->expects($this->once())
                          ->method('getAllNames')
                          ->willReturn($allModNames);

        /* @var ExportModWithDependenciesCommand|MockObject $command */
        $command = $this->getMockBuilder(ExportModWithDependenciesCommand::class)
                        ->setMethods(['hasDependency'])
                        ->setConstructorArgs([$this->dependencyResolver, $this->modRegistry])
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
     * Tests the hasDependency method.
     * @throws ReflectionException
     * @covers ::hasDependency
     */
    public function testHasDependencyWithMandatoryDependency(): void
    {
        $requiredModName = 'abc';
        $modNameToCheck = 'def';
        $mandatoryDependencies = ['abc', 'ghi'];

        $this->dependencyResolver->expects($this->once())
                                 ->method('resolveMandatoryDependencies')
                                 ->with($this->equalTo([$modNameToCheck]))
                                 ->willReturn($mandatoryDependencies);
        $this->dependencyResolver->expects($this->never())
                                 ->method('resolveOptionalDependencies');

        $command = new ExportModWithDependenciesCommand($this->dependencyResolver, $this->modRegistry);
        $result = $this->invokeMethod($command, 'hasDependency', $requiredModName, $modNameToCheck);

        $this->assertTrue($result);
    }

    /**
     * Tests the hasDependency method.
     * @throws ReflectionException
     * @covers ::hasDependency
     */
    public function testHasDependencyWithOptionalDependency(): void
    {
        $requiredModName = 'abc';
        $modNameToCheck = 'def';
        $mandatoryDependencies = ['def', 'ghi'];
        $optionalDependencies = ['abc', 'jkl'];

        $this->dependencyResolver->expects($this->once())
                                 ->method('resolveMandatoryDependencies')
                                 ->with($this->equalTo([$modNameToCheck]))
                                 ->willReturn($mandatoryDependencies);
        $this->dependencyResolver->expects($this->once())
                                 ->method('resolveOptionalDependencies')
                                 ->with($this->equalTo([$modNameToCheck]), $this->identicalTo($mandatoryDependencies))
                                 ->willReturn($optionalDependencies);

        $command = new ExportModWithDependenciesCommand($this->dependencyResolver, $this->modRegistry);
        $result = $this->invokeMethod($command, 'hasDependency', $requiredModName, $modNameToCheck);

        $this->assertTrue($result);
    }

    /**
     * Tests the hasDependency method.
     * @throws ReflectionException
     * @covers ::hasDependency
     */
    public function testHasDependencyWithoutDependency(): void
    {
        $requiredModName = 'abc';
        $modNameToCheck = 'def';
        $mandatoryDependencies = ['def', 'ghi'];
        $optionalDependencies = ['jkl'];

        $this->dependencyResolver->expects($this->once())
                                 ->method('resolveMandatoryDependencies')
                                 ->with($this->equalTo([$modNameToCheck]))
                                 ->willReturn($mandatoryDependencies);
        $this->dependencyResolver->expects($this->once())
                                 ->method('resolveOptionalDependencies')
                                 ->with($this->equalTo([$modNameToCheck]), $this->identicalTo($mandatoryDependencies))
                                 ->willReturn($optionalDependencies);

        $command = new ExportModWithDependenciesCommand($this->dependencyResolver, $this->modRegistry);
        $result = $this->invokeMethod($command, 'hasDependency', $requiredModName, $modNameToCheck);

        $this->assertFalse($result);
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

        $this->dependencyResolver->expects($this->once())
                                 ->method('resolveMandatoryDependencies')
                                 ->with($modNames)
                                 ->willReturn($resultDependencies);

        $command = new ExportModWithDependenciesCommand($this->dependencyResolver, $this->modRegistry);
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
                        ->setConstructorArgs([$this->dependencyResolver, $this->modRegistry])
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
