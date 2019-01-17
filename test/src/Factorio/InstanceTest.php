<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Factorio;

use BluePsyduck\Common\Data\DataContainer;
use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Factorio\DumpExtractor;
use FactorioItemBrowser\Export\Factorio\Instance;
use FactorioItemBrowser\ExportData\Entity\Mod;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;
use FactorioItemBrowser\ExportData\Registry\ModRegistry;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Symfony\Component\Process\Process;

/**
 * The PHPUnit test of the Instance class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Factorio\Instance
 */
class InstanceTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        /* @var DumpExtractor $dumpExtractor */
        $dumpExtractor = $this->createMock(DumpExtractor::class);
        /* @var ModRegistry $modRegistry */
        $modRegistry = $this->createMock(ModRegistry::class);
        $factorioDirectory = 'abc';

        $instance = new Instance($dumpExtractor, $modRegistry, $factorioDirectory);

        $this->assertSame($dumpExtractor, $this->extractProperty($instance, 'dumpExtractor'));
        $this->assertSame($modRegistry, $this->extractProperty($instance, 'modRegistry'));
        $this->assertSame($factorioDirectory, $this->extractProperty($instance, 'factorioDirectory'));
    }

    /**
     * Provides the data for the run test.
     * @return array
     */
    public function provideRun(): array
    {
        return [
            [false],
            [true],
        ];
    }

    /**
     * Tests the run method.
     * @param bool $withException
     * @throws ExportException
     * @covers ::run
     * @dataProvider provideRun
     */
    public function testRun(bool $withException): void
    {
        $hash = 'abc';
        $output = 'def';
        $dump = new DataContainer(['ghi' => 'jkl']);

        /* @var Combination|MockObject $combination */
        $combination = $this->getMockBuilder(Combination::class)
                            ->setMethods(['calculateHash'])
                            ->disableOriginalConstructor()
                            ->getMock();
        $combination->expects($this->once())
                    ->method('calculateHash')
                    ->willReturn($hash);

        /* @var DumpExtractor|MockObject $dumpExtractor */
        $dumpExtractor = $this->getMockBuilder(DumpExtractor::class)
                              ->setMethods(['extract'])
                              ->disableOriginalConstructor()
                              ->getMock();
        $dumpExtractor->expects($withException ? $this->never() : $this->once())
                      ->method('extract')
                      ->with($output)
                      ->willReturn($dump);

        /* @var ModRegistry $modRegistry */
        $modRegistry = $this->createMock(ModRegistry::class);

        /* @var Instance|MockObject $instance */
        $instance = $this->getMockBuilder(Instance::class)
                         ->setMethods(['setUp', 'setUpMods', 'execute', 'removeInstanceDirectory'])
                         ->setConstructorArgs([$dumpExtractor, $modRegistry, 'foo'])
                         ->getMock();
        if ($withException) {
            $instance->expects($this->once())
                     ->method('setUp')
                     ->with($hash)
                     ->willThrowException(new ExportException());
        } else {
            $instance->expects($this->once())
                     ->method('setUp')
                     ->with($hash);
        }
        $instance->expects($withException ? $this->never() : $this->once())
                 ->method('setUpMods')
                 ->with($combination);
        $instance->expects($withException ? $this->never() : $this->once())
                 ->method('execute')
                 ->willReturn($output);
        $instance->expects($this->once())
                 ->method('removeInstanceDirectory');

        if ($withException) {
            $this->expectException(ExportException::class);
        }

        $result = $instance->run($combination);
        $this->assertSame($dump, $result);
    }

    /**
     * Tests the setUp method.
     * @throws ReflectionException
     * @covers ::setUp
     */
    public function testSetUp(): void
    {
        $combinationHash = 'abc';
        $factorioDirectory = 'def';
        $expectedInstanceDirectory = 'def/instances/abc';

        /* @var DumpExtractor $dumpExtractor */
        $dumpExtractor = $this->createMock(DumpExtractor::class);
        /* @var ModRegistry $modRegistry */
        $modRegistry = $this->createMock(ModRegistry::class);

        /* @var Instance|MockObject $instance */
        $instance = $this->getMockBuilder(Instance::class)
                         ->setMethods(['removeInstanceDirectory', 'createDirectory', 'copy', 'createSymlink'])
                         ->setConstructorArgs([$dumpExtractor, $modRegistry, $factorioDirectory])
                         ->getMock();
        $instance->expects($this->once())
                 ->method('removeInstanceDirectory');
        $instance->expects($this->exactly(2))
                 ->method('createDirectory')
                 ->withConsecutive(
                     ['bin/x64'],
                     ['mods']
                 );
        $instance->expects($this->exactly(2))
                 ->method('copy')
                 ->withConsecutive(
                     ['bin/x64/factorio'],
                     ['config-path.cfg']
                 );
        $instance->expects($this->once())
                 ->method('createSymlink')
                 ->with('data');

        $this->invokeMethod($instance, 'setUp', $combinationHash);
        $this->assertSame($expectedInstanceDirectory, $this->extractProperty($instance, 'instanceDirectory'));
    }

    /**
     * Provides the data for the setUpMods test.
     * @return array
     */
    public function provideSetUpMods(): array
    {
        return [
            [false],
            [true],
        ];
    }

    /**
     * Tests the setUpMods method.
     * @param bool $withException
     * @throws ReflectionException
     * @covers ::setUpMods
     * @dataProvider provideSetUpMods
     */
    public function testSetUpMods(bool $withException): void
    {
        $mod1 = (new Mod())->setFileName('abc.zip');
        $mod2 = (new Mod())->setFileName('def.zip');
        $modNames = ['abc', 'def'];
        $combination = (new Combination())->setLoadedModNames($modNames);

        /* @var ModRegistry|MockObject $modRegistry */
        $modRegistry = $this->getMockBuilder(ModRegistry::class)
                            ->setMethods(['get'])
                            ->disableOriginalConstructor()
                            ->getMock();
        if ($withException) {
            $modRegistry->expects($this->once())
                        ->method('get')
                        ->with('abc')
                        ->willReturn(null);
        } else {
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
        }

        /* @var DumpExtractor $dumpExtractor */
        $dumpExtractor = $this->createMock(DumpExtractor::class);

        /* @var Instance|MockObject $instance */
        $instance = $this->getMockBuilder(Instance::class)
                         ->setMethods(['createSymlink'])
                         ->setConstructorArgs([$dumpExtractor, $modRegistry, 'foo'])
                         ->getMock();
        $instance->expects($withException ? $this->never() : $this->exactly(3))
                 ->method('createSymlink')
                 ->withConsecutive(
                     ['mods/abc.zip'],
                     ['mods/def.zip'],
                     ['mods/Dump_1.0.0']
                 );

        if ($withException) {
            $this->expectException(ExportException::class);
        }

        $this->invokeMethod($instance, 'setUpMods', $combination);
    }

    /**
     * Tests the execute method.
     * @throws ReflectionException
     * @covers ::execute
     */
    public function testExecute(): void
    {
        $output = 'abc';

        /* @var Process|MockObject $process */
        $process = $this->getMockBuilder(Process::class)
                        ->setMethods(['run', 'getOutput'])
                        ->disableOriginalConstructor()
                        ->getMock();
        $process->expects($this->once())
                ->method('run');
        $process->expects($this->once())
                ->method('getOutput')
                ->willReturn($output);

        /* @var Instance|MockObject $instance */
        $instance = $this->getMockBuilder(Instance::class)
                         ->setMethods(['createProcess'])
                         ->disableOriginalConstructor()
                         ->getMock();
        $instance->expects($this->once())
                 ->method('createProcess')
                 ->willReturn($process);

        $result = $this->invokeMethod($instance, 'execute');
        $this->assertSame($output, $result);
    }

    /**
     * Tests the createProcess method.
     * @throws ReflectionException
     * @covers ::createProcess
     */
    public function testCreateProcess(): void
    {
        $expectedCommandLine = "'abc' '--no-log-rotation' '--create=dump' '--mod-directory=def'";

        /* @var Instance|MockObject $instance */
        $instance = $this->getMockBuilder(Instance::class)
                         ->setMethods(['getInstancePath'])
                         ->disableOriginalConstructor()
                         ->getMock();
        $instance->expects($this->exactly(2))
                 ->method('getInstancePath')
                 ->withConsecutive(
                     ['bin/x64/factorio'],
                     ['mods']
                 )
                 ->willReturnOnConsecutiveCalls(
                     'abc',
                     'def'
                 );

        /* @var Process $result */
        $result = $this->invokeMethod($instance, 'createProcess');

        $this->assertSame($expectedCommandLine, $result->getCommandLine());
    }

    /**
     * Tests the removeInstanceDirectory method.
     * @throws ReflectionException
     * @covers ::removeInstanceDirectory
     */
    public function testRemoveInstanceDirectory(): void
    {
        $directory = vfsStream::setup('root', null, [
            'instance' => [
                'abc' => [
                    'def' => 'ghi',
                    'jkl' => [
                        'mno' => 'pqr'
                    ]
                ],
                'stu' => 'vwx'
            ]
        ]);
        $directoriesToRemove = [
            'instance/abc/def',
            'instance/abc/jkl/mno',
            'instance/stu',
            'instance',
        ];
        foreach ($directoriesToRemove as $path) {
            $this->assertTrue($directory->hasChild($path));
        }

        /* @var DumpExtractor $dumpExtractor */
        $dumpExtractor = $this->createMock(DumpExtractor::class);
        /* @var ModRegistry $modRegistry */
        $modRegistry = $this->createMock(ModRegistry::class);

        $instance = new Instance($dumpExtractor, $modRegistry, 'foo');
        $this->injectProperty($instance, 'instanceDirectory', vfsStream::url('root/instance'));

        $this->invokeMethod($instance, 'removeInstanceDirectory');
        foreach ($directoriesToRemove as $path) {
            $this->assertFalse($directory->hasChild($path));
        }
    }

    /**
     * Tests the createDirectory method.
     * @throws ReflectionException
     * @covers ::createDirectory
     */
    public function testCreateDirectory(): void
    {
        $directory = vfsStream::setup('root');

        /* @var Instance|MockObject $instance */
        $instance = $this->getMockBuilder(Instance::class)
                         ->setMethods(['getInstancePath'])
                         ->disableOriginalConstructor()
                         ->getMock();
        $instance->expects($this->once())
                 ->method('getInstancePath')
                 ->with('abc')
                 ->willReturn(vfsStream::url('root/abc'));

        $this->assertFalse($directory->hasChild('abc'));
        $this->invokeMethod($instance, 'createDirectory', 'abc');
        $this->assertTrue($directory->hasChild('abc'));
    }

    /**
     * Tests the copy method.
     * @throws ReflectionException
     * @covers ::copy
     */
    public function testCopy(): void
    {
        $root = vfsStream::setup('root', null, [
            'factorio' => [
                'abc' => 'def',
            ],
            'instance' => [
                'ghi' => 'jkl',
            ],
        ]);

        $directoryOrFile = 'abc';
        $factorioPath = vfsStream::url('root/factorio/abc');
        $instancePath = vfsStream::url('root/instance/abc');

        /* @var Instance|MockObject $instance */
        $instance = $this->getMockBuilder(Instance::class)
                         ->setMethods(['getFactorioPath', 'getInstancePath'])
                         ->disableOriginalConstructor()
                         ->getMock();
        $instance->expects($this->once())
                 ->method('getFactorioPath')
                 ->with($directoryOrFile)
                 ->willReturn($factorioPath);
        $instance->expects($this->once())
                 ->method('getInstancePath')
                 ->with($directoryOrFile)
                 ->willReturn($instancePath);

        $this->assertFalse($root->hasChild('instance/abc'));
        $this->invokeMethod($instance, 'copy', $directoryOrFile);
        $this->assertTrue($root->hasChild('instance/abc'));
    }

    /**
     * Tests the getFactorioPath method.
     * @throws ReflectionException
     * @covers ::getFactorioPath
     */
    public function testGetFactorioPath(): void
    {
        $factorioDirectory = 'abc';
        $directoryOrFile = 'def';
        $expectedResult = 'abc/def';

        /* @var DumpExtractor $dumpExtractor */
        $dumpExtractor = $this->createMock(DumpExtractor::class);
        /* @var ModRegistry $modRegistry */
        $modRegistry = $this->createMock(ModRegistry::class);

        $instance = new Instance($dumpExtractor, $modRegistry, $factorioDirectory);

        $result = $this->invokeMethod($instance, 'getFactorioPath', $directoryOrFile);
        $this->assertSame($expectedResult, $result);
    }
    
    /**
     * Tests the getInstancePath method.
     * @throws ReflectionException
     * @covers ::getInstancePath
     */
    public function testGetInstancePath(): void
    {
        $instanceDirectory = 'abc';
        $directoryOrFile = 'def';
        $expectedResult = 'abc/def';

        /* @var DumpExtractor $dumpExtractor */
        $dumpExtractor = $this->createMock(DumpExtractor::class);
        /* @var ModRegistry $modRegistry */
        $modRegistry = $this->createMock(ModRegistry::class);

        $instance = new Instance($dumpExtractor, $modRegistry, 'foo');
        $this->injectProperty($instance, 'instanceDirectory', $instanceDirectory);

        $result = $this->invokeMethod($instance, 'getInstancePath', $directoryOrFile);
        $this->assertSame($expectedResult, $result);
    }
}
