<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Command\Lists;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Export\Command\Lists\ListMissingCommand;
use FactorioItemBrowser\Export\Console\Console;
use FactorioItemBrowser\ExportData\Entity\Mod;
use FactorioItemBrowser\ExportData\Entity\Mod\Dependency;
use FactorioItemBrowser\ExportData\Registry\ModRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Zend\Console\ColorInterface;
use ZF\Console\Route;

/**
 * The PHPUnit test of the ListMissingCommand class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Command\Lists\ListMissingCommand
 */
class ListMissingCommandTest extends TestCase
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

        $command = new ListMissingCommand($modRegistry);

        $this->assertSame($modRegistry, $this->extractProperty($command, 'modRegistry'));
    }

    /**
     * Provides the data for the execute test.
     * @return array
     */
    public function provideExecute(): array
    {
        return [
            [[], false, true, false],
            [['abc', 'def'], true, false, true],
        ];
    }

    /**
     * Tests the execute method.
     * @param array $missingModNames
     * @param bool $expectBanner
     * @param bool $expectWriteLine
     * @param bool $expectPrint
     * @throws ReflectionException
     * @covers ::execute
     * @dataProvider provideExecute
     */
    public function testExecute(
        array $missingModNames,
        bool $expectBanner,
        bool $expectWriteLine,
        bool $expectPrint
    ): void {
        /* @var Console|MockObject $console */
        $console = $this->getMockBuilder(Console::class)
                        ->setMethods(['writeBanner', 'writeLine'])
                        ->disableOriginalConstructor()
                        ->getMock();
        $console->expects($expectBanner ? $this->once() : $this->never())
                ->method('writeBanner')
                ->with('Missing mandatory mods:', ColorInterface::RED);
        $console->expects($expectWriteLine ? $this->once() : $this->never())
                ->method('writeLine')
                ->with('There are no missing mandatory mods.', ColorInterface::GREEN);

        /* @var ListMissingCommand|MockObject $command */
        $command = $this->getMockBuilder(ListMissingCommand::class)
                        ->setMethods(['checkForMissingMods', 'printMissingModNames'])
                        ->disableOriginalConstructor()
                        ->getMock();
        $command->expects($this->once())
                ->method('checkForMissingMods')
                ->willReturn($missingModNames);
        $command->expects($expectPrint ? $this->once() : $this->never())
                ->method('printMissingModNames')
                ->with($missingModNames);
        $this->injectProperty($command, 'console', $console);

        /* @var Route $route */
        $route = $this->createMock(Route::class);

        $this->invokeMethod($command, 'execute', $route);
    }

    /**
     * Tests the checkForMissingMods method.
     * @throws ReflectionException
     * @covers ::checkForMissingMods
     */
    public function testCheckForMissingMods(): void
    {
        $dependency1a = new Dependency();
        $dependency1a->setIsMandatory(true)
                     ->setRequiredModName('def')
                     ->setRequiredVersion('1.2.3');
        $dependency1b = new Dependency();
        $dependency1b->setIsMandatory(true)
                     ->setRequiredModName('jkl')
                     ->setRequiredVersion('2.3.4');
        $dependency1c = new Dependency();
        $dependency1c->setIsMandatory(true)
                     ->setRequiredModName('mno')
                     ->setRequiredVersion('3.4.5');
        $mod1 = new Mod();
        $mod1->setName('abc')
             ->addDependency($dependency1a)
             ->addDependency($dependency1b)
             ->addDependency($dependency1c);

        $dependency2a = new Dependency();
        $dependency2a->setIsMandatory(true)
                     ->setRequiredModName('abc')
                     ->setRequiredVersion('4.5.6');
        $dependency2b = new Dependency();
        $dependency2b->setIsMandatory(true)
                     ->setRequiredModName('ghi')
                     ->setRequiredVersion('5.6.7');
        $dependency2c = new Dependency();
        $dependency2c->setIsMandatory(false)
                     ->setRequiredModName('jkl')
                     ->setRequiredVersion('7.8.9');
        $mod2 = new Mod();
        $mod2->setName('def')
             ->addDependency($dependency2a)
             ->addDependency($dependency2b)
             ->addDependency($dependency2c);

        $dependency3a = new Dependency();
        $dependency3a->setIsMandatory(true)
                     ->setRequiredModName('jkl')
                     ->setRequiredVersion('6.7.8');
        $dependency3b = new Dependency();
        $dependency3b->setIsMandatory(true)
                     ->setRequiredModName('mno')
                     ->setRequiredVersion('0.1.2');
        $mod3 = new Mod();
        $mod3->setName('ghi')
             ->addDependency($dependency3a)
             ->addDependency($dependency3b);

        $allModNames = ['abc', 'def', 'ghi'];
        $expectedResult = [
            'jkl' => '6.7.8',
            'mno' => '3.4.5',
        ];

        /* @var ModRegistry|MockObject $modRegistry */
        $modRegistry = $this->getMockBuilder(ModRegistry::class)
                            ->setMethods(['getAllNames', 'get'])
                            ->disableOriginalConstructor()
                            ->getMock();
        $modRegistry->expects($this->once())
                    ->method('getAllNames')
                    ->willReturn($allModNames);
        $modRegistry->expects($this->exactly(3))
                    ->method('get')
                    ->withConsecutive(
                        ['abc'],
                        ['def'],
                        ['ghi']
                    )
                    ->willReturnOnConsecutiveCalls(
                        $mod1,
                        $mod2,
                        $mod3
                    );

        $command = new ListMissingCommand($modRegistry);
        $result = $this->invokeMethod($command, 'checkForMissingMods');

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the printMissingModNames method.
     * @throws ReflectionException
     * @covers ::printMissingModNames
     */
    public function testPrintMissingModNames(): void
    {
        $missingModNames = [
            'abc' => '1.2.3',
            'def' => '2.3.4',
        ];

        /* @var Console|MockObject $console */
        $console = $this->getMockBuilder(Console::class)
                        ->setMethods(['formatModName', 'formatVersion', 'writeLine'])
                        ->disableOriginalConstructor()
                        ->getMock();
        $console->expects($this->exactly(2))
                ->method('formatModName')
                ->withConsecutive(
                    ['abc'],
                    ['def']
                )
                ->willReturnOnConsecutiveCalls(
                    'ABC',
                    'DEF'
                );
        $console->expects($this->exactly(2))
                ->method('formatVersion')
                ->withConsecutive(
                    ['1.2.3'],
                    ['2.3.4']
                )
                ->willReturnOnConsecutiveCalls(
                    '3.2.1',
                    '4.3.2'
                );
        $console->expects($this->exactly(2))
                ->method('writeLine')
                ->withConsecutive(
                    ['ABC: 3.2.1'],
                    ['DEF: 4.3.2']
                );

        /* @var ModRegistry $modRegistry */
        $modRegistry = $this->createMock(ModRegistry::class);

        $command = new ListMissingCommand($modRegistry);
        $this->injectProperty($command, 'console', $console);

        $this->invokeMethod($command, 'printMissingModNames', $missingModNames);
    }
}
