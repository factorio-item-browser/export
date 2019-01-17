<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Command\Lists;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Export\Command\Lists\ListCommand;
use FactorioItemBrowser\Export\Console\Console;
use FactorioItemBrowser\ExportData\Entity\Mod;
use FactorioItemBrowser\ExportData\Registry\ModRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Zend\Console\ColorInterface;
use ZF\Console\Route;

/**
 * The PHPUnit test of the ListCommand class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Command\Lists\ListCommand
 */
class ListCommandTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        /* @var ModRegistry $availableModRegistry */
        $availableModRegistry = $this->createMock(ModRegistry::class);
        /* @var ModRegistry $exportedModRegistry */
        $exportedModRegistry = $this->createMock(ModRegistry::class);

        $command = new ListCommand($availableModRegistry, $exportedModRegistry);

        $this->assertSame($availableModRegistry, $this->extractProperty($command, 'availableModRegistry'));
        $this->assertSame($exportedModRegistry, $this->extractProperty($command, 'exportedModRegistry'));
    }

    /**
     * Tests the execute method.
     * @throws ReflectionException
     * @covers ::execute
     */
    public function testExecute(): void
    {
        $mod1 = (new Mod())->setName('abc');
        $mod2 = (new Mod())->setName('def');
        $orderedMods = [$mod1, $mod2];
        $exportedMod = (new Mod())->setName('ghi');

        /* @var ModRegistry|MockObject $exportedModRegistry */
        $exportedModRegistry = $this->getMockBuilder(ModRegistry::class)
                                    ->setMethods(['get'])
                                    ->disableOriginalConstructor()
                                    ->getMock();
        $exportedModRegistry->expects($this->exactly(2))
                            ->method('get')
                            ->withConsecutive(
                                ['abc'],
                                ['def']
                            )
                            ->willReturnOnConsecutiveCalls(
                                $exportedMod,
                                null
                            );

        /* @var ModRegistry $availableModRegistry */
        $availableModRegistry = $this->createMock(ModRegistry::class);

        /* @var ListCommand|MockObject $command */
        $command = $this->getMockBuilder(ListCommand::class)
                        ->setMethods(['getOrderedMods', 'printMod'])
                        ->setConstructorArgs([$availableModRegistry, $exportedModRegistry])
                        ->getMock();
        $command->expects($this->once())
                ->method('getOrderedMods')
                ->willReturn($orderedMods);
        $command->expects($this->exactly(2))
                ->method('printMod')
                ->withConsecutive(
                    [$mod1, $exportedMod],
                    [$mod2, null]
                );

        /* @var Route $route */
        $route = $this->createMock(Route::class);

        $this->invokeMethod($command, 'execute', $route);
    }

    /**
     * Tests the getOrderedMods method.
     * @throws ReflectionException
     * @covers ::getOrderedMods
     */
    public function testGetOrderedMods(): void
    {
        $mod1 = (new Mod())->setOrder(42);
        $mod2 = (new Mod())->setOrder(1337);
        $mod3 = (new Mod())->setOrder(21);
        $allModNames = ['abc', 'def', 'ghi'];
        $expectedResult = [$mod3, $mod1, $mod2];
        
        /* @var ModRegistry|MockObject $availableModRegistry */
        $availableModRegistry = $this->getMockBuilder(ModRegistry::class)
                                     ->setMethods(['getAllNames', 'get'])
                                     ->disableOriginalConstructor()
                                     ->getMock();
        $availableModRegistry->expects($this->once())
                             ->method('getAllNames')
                             ->willReturn($allModNames);
        $availableModRegistry->expects($this->exactly(3))
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

        /* @var ModRegistry $exportedModRegistry */
        $exportedModRegistry = $this->createMock(ModRegistry::class);

        $command = new ListCommand($availableModRegistry, $exportedModRegistry);
        $result = $this->invokeMethod($command, 'getOrderedMods');

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Provides the data for the printMod test.
     * @return array
     */
    public function providePrintMod(): array
    {
        return [
            ['1.2.3', '1.2.3', '1.2.3', null],
            ['1.2.3', '4.5.6', '4.5.6', ColorInterface::LIGHT_YELLOW],
            ['1.2.3', null, '', ColorInterface::LIGHT_CYAN],
        ];
    }

    /**
     * Tests the printMod method.
     * @param string $availableVersion
     * @param string|null $exportedVersion
     * @param string $expectedExportedVersion
     * @param int|null $expectedColor
     * @throws ReflectionException
     * @covers ::printMod
     * @dataProvider providePrintMod
     */
    public function testPrintMod(
        string $availableVersion,
        ?string $exportedVersion,
        string $expectedExportedVersion,
        ?int $expectedColor
    ): void {
        $availableMod = new Mod();
        $availableMod->setName('abc')
                     ->setVersion($availableVersion);

        $exportedMod = null;
        if ($exportedVersion !== null) {
            $exportedMod = new Mod();
            $exportedMod->setVersion($exportedVersion);
        }

        /* @var Console|MockObject $console */
        $console = $this->getMockBuilder(Console::class)
                        ->setMethods(['writeLine', 'formatModName', 'formatVersion'])
                        ->disableOriginalConstructor()
                        ->getMock();
        $console->expects($this->once())
                ->method('formatModName')
                ->with('abc')
                ->willReturn('ABC');
        $console->expects($this->exactly(2))
                ->method('formatVersion')
                ->withConsecutive(
                    [$expectedExportedVersion],
                    [$availableVersion]
                )
                ->willReturnOnConsecutiveCalls(
                    '6.5.4',
                    '9.8.7'
                );
        $console->expects($this->once())
                ->method('writeLine')
                ->with('ABC: 6.5.4 -> 9.8.7', $expectedColor);

        /* @var ModRegistry $availableModRegistry */
        $availableModRegistry = $this->createMock(ModRegistry::class);
        /* @var ModRegistry $exportedModRegistry */
        $exportedModRegistry = $this->createMock(ModRegistry::class);

        $command = new ListCommand($availableModRegistry, $exportedModRegistry);
        $this->injectProperty($command, 'console', $console);

        $this->invokeMethod($command, 'printMod', $availableMod, $exportedMod);
    }
}
