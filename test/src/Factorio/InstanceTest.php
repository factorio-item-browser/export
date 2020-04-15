<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Factorio;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Common\Constant\Constant;
use FactorioItemBrowser\Export\Console\Console;
use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\Entity\InfoJson;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Factorio\DumpExtractor;
use FactorioItemBrowser\Export\Factorio\Instance;
use FactorioItemBrowser\Export\Mod\ModFileManager;
use JMS\Serializer\SerializerInterface;
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
     * The mocked console.
     * @var Console&MockObject
     */
    protected $console;

    /**
     * The mocked dump extractor.
     * @var DumpExtractor&MockObject
     */
    protected $dumpExtractor;

    /**
     * The mocked mod file manager.
     * @var ModFileManager&MockObject
     */
    protected $modFileManager;

    /**
     * The mocked serializer.
     * @var SerializerInterface&MockObject
     */
    protected $serializer;

    /**
     * Sets up the test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->console = $this->createMock(Console::class);
        $this->dumpExtractor = $this->createMock(DumpExtractor::class);
        $this->modFileManager = $this->createMock(ModFileManager::class);
        $this->serializer = $this->createMock(SerializerInterface::class);
    }


    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $factorioDirectory = 'foo';
        $instancesDirectory = 'bar';

        $instance = new Instance(
            $this->console,
            $this->dumpExtractor,
            $this->modFileManager,
            $this->serializer,
            $factorioDirectory,
            $instancesDirectory
        );

        $this->assertSame($this->console, $this->extractProperty($instance, 'console'));
        $this->assertSame($this->dumpExtractor, $this->extractProperty($instance, 'dumpExtractor'));
        $this->assertSame($this->modFileManager, $this->extractProperty($instance, 'modFileManager'));
        $this->assertSame($this->serializer, $this->extractProperty($instance, 'serializer'));
        $this->assertSame($factorioDirectory, $this->extractProperty($instance, 'factorioDirectory'));
        $this->assertSame($instancesDirectory, $this->extractProperty($instance, 'instancesDirectory'));
    }

    /**
     * Tests the run method.
     * @throws ExportException
     * @throws ReflectionException
     * @covers ::run
     */
    public function testRun(): void
    {
        $instancesDirectory = 'abc';
        $combinationId = 'def';
        $modNames = ['ghi', 'jkl'];
        $output = 'mno';
        $expectedCombinationInstanceDirectory = 'abc/def';

        /* @var Dump&MockObject $dump */
        $dump = $this->createMock(Dump::class);

        $this->console->expects($this->exactly(3))
                      ->method('writeAction')
                      ->withConsecutive(
                          [$this->identicalTo('Preparing Factorio instance')],
                          [$this->identicalTo('Launching Factorio')],
                          [$this->identicalTo('Extracting dumped data')]
                      );

        $this->dumpExtractor->expects($this->once())
                            ->method('extract')
                            ->with($this->identicalTo($output))
                            ->willReturn($dump);

        /* @var Instance&MockObject $instance */
        $instance = $this->getMockBuilder(Instance::class)
                         ->onlyMethods([
                             'setupInstance',
                             'setUpMods',
                             'setUpDumpMod',
                             'execute',
                             'removeInstanceDirectory',
                         ])
                         ->setConstructorArgs([
                             $this->console,
                             $this->dumpExtractor,
                             $this->modFileManager,
                             $this->serializer,
                             'foo',
                             $instancesDirectory,
                         ])
                         ->getMock();
        $instance->expects($this->once())
                 ->method('setUpInstance');
        $instance->expects($this->once())
                 ->method('setUpMods')
                 ->with($this->identicalTo($modNames));
        $instance->expects($this->once())
                 ->method('setUpDumpMod')
                 ->with($this->identicalTo($modNames));
        $instance->expects($this->once())
                 ->method('execute')
                 ->willReturn($output);
        $instance->expects($this->once())
                 ->method('removeInstanceDirectory');

        $result = $instance->run($combinationId, $modNames);

        $this->assertSame($dump, $result);
        $this->assertSame(
            $expectedCombinationInstanceDirectory,
            $this->extractProperty($instance, 'combinationInstanceDirectory')
        );
    }

    /**
     * Tests the setUpInstance method.
     * @throws ReflectionException
     * @covers ::setUpInstance
     */
    public function testSetUpInstance(): void
    {
        /* @var Instance&MockObject $instance */
        $instance = $this->getMockBuilder(Instance::class)
                         ->onlyMethods(['removeInstanceDirectory', 'createDirectory', 'copy', 'createFactorioSymlink'])
                         ->disableOriginalConstructor()
                         ->getMock();
        $instance->expects($this->once())
                 ->method('removeInstanceDirectory');
        $instance->expects($this->exactly(2))
                 ->method('createDirectory')
                 ->withConsecutive(
                     [$this->identicalTo('bin/x64')],
                     [$this->identicalTo('mods')]
                 );
        $instance->expects($this->exactly(2))
                 ->method('copy')
                 ->withConsecutive(
                     [$this->identicalTo('bin/x64/factorio')],
                     [$this->identicalTo('config-path.cfg')]
                 );
        $instance->expects($this->once())
                 ->method('createFactorioSymlink')
                 ->with($this->identicalTo('data'));

        $this->invokeMethod($instance, 'setUpInstance');
    }

    /**
     * Tests the setUpMods method.
     * @throws ReflectionException
     * @covers ::setUpMods
     */
    public function testSetUpMods(): void
    {
        $modNames = ['abc', 'def'];

        /* @var Instance&MockObject $instance */
        $instance = $this->getMockBuilder(Instance::class)
                         ->onlyMethods(['createModSymlink'])
                         ->disableOriginalConstructor()
                         ->getMock();
        $instance->expects($this->exactly(2))
                 ->method('createModSymlink')
                 ->withConsecutive(
                     [$this->identicalTo('abc')],
                     [$this->identicalTo('def')]
                 );

        $this->invokeMethod($instance, 'setUpMods', $modNames);
    }

    /**
     * Tests the createDumpInfoJson method.
     * @throws ReflectionException
     * @covers ::createDumpInfoJson
     */
    public function testCreateDumpInfoJson(): void
    {
        $baseVersion = '1.2.3';
        $modNames = ['abc', 'def'];

        /* @var InfoJson&MockObject $baseInfo */
        $baseInfo = $this->createMock(InfoJson::class);
        $baseInfo->expects($this->once())
                 ->method('getVersion')
                 ->willReturn($baseVersion);

        $this->modFileManager->expects($this->once())
                             ->method('getInfo')
                             ->with($this->identicalTo(Constant::MOD_NAME_BASE))
                             ->willReturn($baseInfo);

        $expectedResult = new InfoJson();
        $expectedResult->setName('Dump')
                       ->setAuthor('factorio-item-browser')
                       ->setVersion('1.0.0')
                       ->setFactorioVersion($baseVersion)
                       ->setDependencies($modNames);

        $instance = new Instance(
            $this->console,
            $this->dumpExtractor,
            $this->modFileManager,
            $this->serializer,
            'foo',
            'bar'
        );

        $result = $this->invokeMethod($instance, 'createDumpInfoJson', $modNames);
        $this->assertEquals($expectedResult, $result);
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
                        ->onlyMethods(['run', 'getOutput'])
                        ->disableOriginalConstructor()
                        ->getMock();
        $process->expects($this->once())
                ->method('run');
        $process->expects($this->once())
                ->method('getOutput')
                ->willReturn($output);

        /* @var Instance|MockObject $instance */
        $instance = $this->getMockBuilder(Instance::class)
                         ->onlyMethods(['createProcess'])
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
        $expectedCommandLine = "'abc' '--no-log-rotation' '--create=def' '--mod-directory=ghi'";

        /* @var Instance|MockObject $instance */
        $instance = $this->getMockBuilder(Instance::class)
                         ->onlyMethods(['getInstancePath'])
                         ->disableOriginalConstructor()
                         ->getMock();
        $instance->expects($this->exactly(3))
                 ->method('getInstancePath')
                 ->withConsecutive(
                     ['bin/x64/factorio'],
                     ['dump'],
                     ['mods']
                 )
                 ->willReturnOnConsecutiveCalls(
                     'abc',
                     'def',
                     'ghi'
                 );

        /* @var Process $result */
        $result = $this->invokeMethod($instance, 'createProcess');

        $this->assertSame($expectedCommandLine, $result->getCommandLine());
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
                         ->onlyMethods(['getInstancePath'])
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
                         ->onlyMethods(['getFactorioPath', 'getInstancePath'])
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
        $fileName = 'def';
        $expectedResult = 'abc/def';

        $instance = new Instance(
            $this->console,
            $this->dumpExtractor,
            $this->modFileManager,
            $this->serializer,
            $factorioDirectory,
            'bar'
        );

        $result = $this->invokeMethod($instance, 'getFactorioPath', $fileName);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the getInstancePath method.
     * @throws ReflectionException
     * @covers ::getInstancePath
     */
    public function testGetInstancePath(): void
    {
        $combinationInstanceDirectory = 'abc';
        $fileName = 'def';
        $expectedResult = 'abc/def';

        $instance = new Instance(
            $this->console,
            $this->dumpExtractor,
            $this->modFileManager,
            $this->serializer,
            'foo',
            'bar'
        );
        $this->injectProperty($instance, 'combinationInstanceDirectory', $combinationInstanceDirectory);

        $result = $this->invokeMethod($instance, 'getInstancePath', $fileName);

        $this->assertSame($expectedResult, $result);
    }
}
