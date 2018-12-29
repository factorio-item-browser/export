<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Command\Update;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Export\Command\Update\UpdateListCommand;
use FactorioItemBrowser\Export\Console\Console;
use FactorioItemBrowser\Export\Constant\CommandName;
use FactorioItemBrowser\Export\Constant\ParameterName;
use FactorioItemBrowser\Export\Mod\ModFileManager;
use FactorioItemBrowser\Export\Mod\ModReader;
use FactorioItemBrowser\ExportData\Entity\Mod;
use FactorioItemBrowser\ExportData\Registry\ModRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Zend\ProgressBar\ProgressBar;
use ZF\Console\Route;

/**
 * The PHPUnit test of the UpdateListCommand class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Command\Update\UpdateListCommand
 */
class UpdateListCommandTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests the constructing.
     * @covers ::__construct
     * @throws ReflectionException
     */
    public function testConstruct(): void
    {
        /* @var ModFileManager $modFileManager */
        $modFileManager = $this->createMock(ModFileManager::class);
        /* @var ModReader $modReader */
        $modReader = $this->createMock(ModReader::class);
        /* @var ModRegistry $modRegistry */
        $modRegistry = $this->createMock(ModRegistry::class);

        $command = new UpdateListCommand($modFileManager, $modReader, $modRegistry);

        $this->assertSame($modFileManager, $this->extractProperty($command, 'modFileManager'));
        $this->assertSame($modReader, $this->extractProperty($command, 'modReader'));
        $this->assertSame($modRegistry, $this->extractProperty($command, 'modRegistry'));
    }

    /**
     * Tests the execute method.
     * @throws ReflectionException
     * @covers ::execute
     */
    public function testExecute(): void
    {
        $modFileNames = ['abc', 'def'];
        $currentMods = [(new Mod())->setName('ghi'), (new Mod())->setName('jkl')];
        $newMods = [(new Mod())->setName('mno'), (new Mod())->setName('pqr')];

        /* @var ModFileManager|MockObject $modFileManager */
        $modFileManager = $this->getMockBuilder(ModFileManager::class)
                               ->setMethods(['getModFileNames'])
                               ->disableOriginalConstructor()
                               ->getMock();
        $modFileManager->expects($this->once())
                       ->method('getModFileNames')
                       ->willReturn($modFileNames);

        /* @var Console|MockObject $console */
        $console = $this->getMockBuilder(Console::class)
                        ->setMethods(['writeAction'])
                        ->disableOriginalConstructor()
                        ->getMock();
        $console->expects($this->exactly(2))
                ->method('writeAction')
                ->withConsecutive(
                    ['Hashing mod files'],
                    ['Persisting mods']
                );

        /* @var ModReader $modReader */
        $modReader = $this->createMock(ModReader::class);
        /* @var ModRegistry $modRegistry */
        $modRegistry = $this->createMock(ModRegistry::class);

        /* @var UpdateListCommand|MockObject $command */
        $command = $this->getMockBuilder(UpdateListCommand::class)
                        ->setMethods(['getModsFromRegistry', 'detectNewMods', 'printChangesToConsole', 'runCommand'])
                        ->setConstructorArgs([$modFileManager, $modReader, $modRegistry])
                        ->getMock();
        $command->expects($this->once())
                ->method('getModsFromRegistry')
                ->willReturn($currentMods);
        $command->expects($this->once())
                ->method('detectNewMods')
                ->with($modFileNames, $currentMods)
                ->willReturn($newMods);
        $command->expects($this->once())
                ->method('printChangesToConsole')
                ->with($newMods, $currentMods);
        $command->expects($this->exactly(3))
                ->method('runCommand')
                ->withConsecutive(
                    [CommandName::UPDATE_DEPENDENCIES, [], $console],
                    [CommandName::UPDATE_ORDER, [], $console],
                    [CommandName::EXPORT_PREPARE, [], $console]
                );
        $this->injectProperty($command, 'console', $console);

        /* @var Route $route */
        $route = $this->createMock(Route::class);

        $this->invokeMethod($command, 'execute', $route);
    }

    /**
     * Tests the getModsFromRegistry method.
     * @throws ReflectionException
     * @covers ::getModsFromRegistry
     */
    public function testGetModsFromRegistry(): void
    {
        $mod1 = (new Mod())->setName('abc');
        $mod2 = (new Mod())->setName('def');
        $modNames = ['abc', 'def'];
        $expectedResult = [
            'abc' => $mod1,
            'def' => $mod2,
        ];

        /* @var ModRegistry|MockObject $modRegistry */
        $modRegistry = $this->getMockBuilder(ModRegistry::class)
                            ->setMethods(['getAllNames', 'get'])
                            ->disableOriginalConstructor()
                            ->getMock();
        $modRegistry->expects($this->once())
                    ->method('getAllNames')
                    ->willReturn($modNames);
        $modRegistry->expects($this->exactly(2))
                    ->method('get')
                    ->withConsecutive(
                        ['abc'],
                        ['def']
                    )
                    ->willReturnOnConsecutiveCalls(
                        $mod1,
                        $mod2
                    );

        /* @var ModFileManager $modFileManager */
        $modFileManager = $this->createMock(ModFileManager::class);
        /* @var ModReader $modReader */
        $modReader = $this->createMock(ModReader::class);

        $command = new UpdateListCommand($modFileManager, $modReader, $modRegistry);
        $result = $this->invokeMethod($command, 'getModsFromRegistry', $modRegistry);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the detectNewMods method.
     * @throws ReflectionException
     * @covers ::detectNewMods
     */
    public function testDetectNewMods(): void
    {
        $modFileNames = ['abc', 'def'];
        $currentMods = [(new Mod())->setName('ghi'), (new Mod())->setName('jkl')];
        $currentModsByChecksum = ['ghi' => (new Mod())->setName('ghi'), 'jkl' => (new Mod())->setName('jkl')];
        $newMod1 = (new Mod())->setName('mno');
        $newMod2 = (new Mod())->setName('pqr');
        $expectedResult = ['mno' => $newMod1, 'pqr' => $newMod2];

        /* @var ProgressBar|MockObject $progressBar */
        $progressBar = $this->getMockBuilder(ProgressBar::class)
                            ->setMethods(['next', 'finish'])
                            ->disableOriginalConstructor()
                            ->getMock();
        $progressBar->expects($this->exactly(2))
                    ->method('next');
        $progressBar->expects($this->once())
                    ->method('finish');

        /* @var Console|MockObject $console */
        $console = $this->getMockBuilder(Console::class)
                        ->setMethods(['createProgressBar'])
                        ->disableOriginalConstructor()
                        ->getMock();
        $console->expects($this->once())
                ->method('createProgressBar')
                ->with(2)
                ->willReturn($progressBar);

        /* @var UpdateListCommand|MockObject $command */
        $command = $this->getMockBuilder(UpdateListCommand::class)
                        ->setMethods(['getModsByChecksum', 'checkModFile'])
                        ->disableOriginalConstructor()
                        ->getMock();
        $command->expects($this->once())
                ->method('getModsByChecksum')
                ->with($currentMods)
                ->willReturn($currentModsByChecksum);
        $command->expects($this->exactly(2))
                ->method('checkModFile')
                ->withConsecutive(
                    ['abc', $currentModsByChecksum],
                    ['def', $currentModsByChecksum]
                )
                ->willReturnOnConsecutiveCalls(
                    $newMod1,
                    $newMod2
                );
        $this->injectProperty($command, 'console', $console);

        $result = $this->invokeMethod($command, 'detectNewMods', $modFileNames, $currentMods);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the getModsByChecksum method.
     * @throws ReflectionException
     * @covers ::getModsByChecksum
     */
    public function testGetModsByChecksum(): void
    {
        $mod1 = (new Mod())->setChecksum('abc');
        $mod2 = (new Mod())->setChecksum('def');
        $mods = [$mod1, $mod2];
        $expectedResult = ['abc' => $mod1, 'def' => $mod2];

        /* @var ModFileManager $modFileManager */
        $modFileManager = $this->createMock(ModFileManager::class);
        /* @var ModReader $modReader */
        $modReader = $this->createMock(ModReader::class);
        /* @var ModRegistry $modRegistry */
        $modRegistry = $this->createMock(ModRegistry::class);

        $command = new UpdateListCommand($modFileManager, $modReader, $modRegistry);
        $result = $this->invokeMethod($command, 'getModsByChecksum', $mods);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Provides the data for the checkModFile test.
     * @return array
     */
    public function provideCheckModFile(): array
    {
        $mod1 = (new Mod())->setName('abc');
        $mod2 = (new Mod())->setName('def');

        return [
            ['ghi', ['jkl' => $mod1, 'mno' => $mod2], 'jkl', null, null, $mod1],
            ['ghi', ['jkl' => $mod1], 'mno', $mod2, 'def', $mod2],
        ];
    }

    /**
     * Tests the checkModFile method.
     * @param string $modFileName
     * @param array $currentModsByChecksum
     * @param string $calculatedChecksum
     * @param Mod|null $readMod
     * @param string|null $cleanCacheModName
     * @param Mod $expectedResult
     * @throws ReflectionException
     * @covers ::checkModFile
     * @dataProvider provideCheckModFile
     */
    public function testCheckModFile(
        string $modFileName,
        array $currentModsByChecksum,
        string $calculatedChecksum,
        ?Mod $readMod,
        ?string $cleanCacheModName,
        Mod $expectedResult
    ): void {
        /* @var ModReader|MockObject $modReader */
        $modReader = $this->getMockBuilder(ModReader::class)
                          ->setMethods(['calculateChecksum', 'read'])
                          ->disableOriginalConstructor()
                          ->getMock();
        $modReader->expects($this->once())
                  ->method('calculateChecksum')
                  ->with($modFileName)
                  ->willReturn($calculatedChecksum);
        $modReader->expects($readMod === null ? $this->never() : $this->once())
                  ->method('read')
                  ->with($modFileName, $calculatedChecksum)
                  ->willReturn($readMod);


        /* @var ModFileManager $modFileManager */
        $modFileManager = $this->createMock(ModFileManager::class);
        /* @var ModRegistry $modRegistry */
        $modRegistry = $this->createMock(ModRegistry::class);

        /* @var UpdateListCommand|MockObject $command */
        $command = $this->getMockBuilder(UpdateListCommand::class)
                        ->setMethods(['runCommand'])
                        ->setConstructorArgs([$modFileManager, $modReader, $modRegistry])
                        ->getMock();
        $command->expects($cleanCacheModName === null ? $this->never() : $this->once())
                ->method('runCommand')
                ->with(CommandName::CLEAN_CACHE, [ParameterName::MOD_NAME => $cleanCacheModName], null);

        $result = $this->invokeMethod($command, 'checkModFile', $modFileName, $currentModsByChecksum);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the setModsToRegistry method.
     * @throws ReflectionException
     * @covers ::setModsToRegistry
     */
    public function testSetModsToRegistry(): void
    {
        $mod1 = (new Mod())->setName('abc');
        $mod2 = (new Mod())->setName('def');
        $allModNames = ['abc', 'ghi'];

        /* @var ModRegistry|MockObject $modRegistry */
        $modRegistry = $this->getMockBuilder(ModRegistry::class)
                            ->setMethods(['getAllNames', 'set', 'remove', 'saveMods'])
                            ->disableOriginalConstructor()
                            ->getMock();
        $modRegistry->expects($this->once())
                    ->method('getAllNames')
                    ->willReturn($allModNames);
        $modRegistry->expects($this->exactly(2))
                    ->method('set')
                    ->withConsecutive(
                        [$mod1],
                        [$mod2]
                    );
        $modRegistry->expects($this->once())
                    ->method('remove')
                    ->with('ghi');
        $modRegistry->expects($this->once())
                    ->method('saveMods');

        /* @var ModFileManager $modFileManager */
        $modFileManager = $this->createMock(ModFileManager::class);
        /* @var ModReader $modReader */
        $modReader = $this->createMock(ModReader::class);

        $command = new UpdateListCommand($modFileManager, $modReader, $modRegistry);

        $this->invokeMethod($command, 'setModsToRegistry', [$mod1, $mod2]);
    }

    /**
     * Tests the printChangesToConsole method.
     * @covers ::printChangesToConsole
     * @throws ReflectionException
     */
    public function testPrintChangesToConsole(): void
    {
        $mod1 = new Mod();
        $mod1->setName('abc')
             ->setChecksum('cba')
             ->setVersion('1.2.3');
        $mod2 = new Mod();
        $mod2->setName('def')
             ->setChecksum('fed')
             ->setVersion('2.3.4');
        $mod3a = new Mod();
        $mod3a->setName('ghi')
              ->setChecksum('ihg')
              ->setVersion('3.4.5');
        $mod3b = new Mod();
        $mod3b->setName('ghi')
              ->setChecksum('lkj')
              ->setVersion('4.5.6');

        $currentMods = [
            'def' => $mod2,
            'ghi' => $mod3a
        ];
        $newMods = [$mod1, $mod2, $mod3b];

        /* @var Console|MockObject $console */
        $console = $this->getMockBuilder(Console::class)
                        ->setMethods(['writeLine', 'formatModName', 'formatVersion'])
                        ->disableOriginalConstructor()
                        ->getMock();
        $console->expects($this->exactly(2))
                ->method('writeLine')
                ->withConsecutive(
                    ['ABC:       -> 3.2.1'],
                    ['GHI: 5.4.3 -> 6.5.4']
                );
        $console->expects($this->exactly(2))
                ->method('formatModName')
                ->withConsecutive(
                    ['abc'],
                    ['ghi']
                )
                ->willReturnOnConsecutiveCalls(
                    'ABC',
                    'GHI'
                );
        $console->expects($this->exactly(4))
                ->method('formatVersion')
                ->withConsecutive(
                    ['', true],
                    ['1.2.3', false],
                    ['3.4.5', true],
                    ['4.5.6', false]
                )
                ->willReturnOnConsecutiveCalls(
                    '     ',
                    '3.2.1',
                    '5.4.3',
                    '6.5.4'
                );

        /* @var ModFileManager $modFileManager */
        $modFileManager = $this->createMock(ModFileManager::class);
        /* @var ModReader $modReader */
        $modReader = $this->createMock(ModReader::class);
        /* @var ModRegistry $modRegistry */
        $modRegistry = $this->createMock(ModRegistry::class);

        $command = new UpdateListCommand($modFileManager, $modReader, $modRegistry);
        $this->injectProperty($command, 'console', $console);

        $this->invokeMethod($command, 'printChangesToConsole', $newMods, $currentMods);
    }
}
