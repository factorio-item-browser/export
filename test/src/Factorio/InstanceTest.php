<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Factorio;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Common\Constant\Constant;
use FactorioItemBrowser\Export\Output\Console;
use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\Entity\InfoJson;
use FactorioItemBrowser\Export\Entity\ModList\Mod;
use FactorioItemBrowser\Export\Entity\ModListJson;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Factorio\Instance;
use FactorioItemBrowser\Export\Service\ModFileService;
use FactorioItemBrowser\Export\Process\FactorioProcess;
use FactorioItemBrowser\Export\Process\FactorioProcessFactory;
use JMS\Serializer\SerializerInterface;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

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

    /** @var Console&MockObject */
    private Console $console;
    /** @var FactorioProcessFactory&MockObject */
    private FactorioProcessFactory $factorioProcessFactory;
    /** @var ModFileService&MockObject */
    private ModFileService $modFileManager;
    /** @var SerializerInterface&MockObject */
    private SerializerInterface $serializer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->console = $this->createMock(Console::class);
        $this->factorioProcessFactory = $this->createMock(FactorioProcessFactory::class);
        $this->modFileManager = $this->createMock(ModFileService::class);
        $this->serializer = $this->createMock(SerializerInterface::class);
    }

    /**
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $factorioDirectory = 'foo';
        $instancesDirectory = 'bar';
        $version = '1.2.3';

        $instance = new Instance(
            $this->console,
            $this->factorioProcessFactory,
            $this->modFileManager,
            $this->serializer,
            $factorioDirectory,
            $instancesDirectory,
            $version
        );

        $this->assertSame($this->console, $this->extractProperty($instance, 'console'));
        $this->assertSame($this->factorioProcessFactory, $this->extractProperty($instance, 'factorioProcessFactory'));
        $this->assertSame($this->modFileManager, $this->extractProperty($instance, 'modFileManager'));
        $this->assertSame($this->serializer, $this->extractProperty($instance, 'serializer'));
        $this->assertSame($factorioDirectory, $this->extractProperty($instance, 'factorioDirectory'));
        $this->assertSame($instancesDirectory, $this->extractProperty($instance, 'instancesDirectory'));
        $this->assertSame($version, $this->extractProperty($instance, 'version'));
    }


    /**
     * @throws ExportException
     * @throws ReflectionException
     * @covers ::run
     */
    public function testRun(): void
    {
        $instancesDirectory = 'abc';
        $combinationId = 'def';
        $modNames = ['ghi', 'jkl'];
        $expectedCombinationInstanceDirectory = 'abc/def';

        $dump = $this->createMock(Dump::class);

        $process = $this->createMock(FactorioProcess::class);
        $process->expects($this->once())
                ->method('run');
        $process->expects($this->once())
                ->method('getDump')
                ->willReturn($dump);

        $this->console->expects($this->exactly(2))
                      ->method('writeAction')
                      ->withConsecutive(
                          [$this->identicalTo('Preparing Factorio instance')],
                          [$this->identicalTo('Executing Factorio')],
                      );

        $this->factorioProcessFactory->expects($this->once())
                                     ->method('create')
                                     ->with($this->identicalTo($expectedCombinationInstanceDirectory))
                                     ->willReturn($process);

        $instance = $this->getMockBuilder(Instance::class)
                         ->onlyMethods([
                             'setupInstance',
                             'setUpMods',
                             'setUpDumpMod',
                             'removeInstanceDirectory',
                         ])
                         ->setConstructorArgs([
                             $this->console,
                             $this->factorioProcessFactory,
                             $this->modFileManager,
                             $this->serializer,
                             'foo',
                             $instancesDirectory,
                             '1.2.3',
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
                 ->method('removeInstanceDirectory');

        $result = $instance->run($combinationId, $modNames);

        $this->assertSame($dump, $result);
        $this->assertSame(
            $expectedCombinationInstanceDirectory,
            $this->extractProperty($instance, 'combinationInstanceDirectory')
        );
    }

    /**
     * @throws ReflectionException
     * @covers ::setUpInstance
     */
    public function testSetUpInstance(): void
    {
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
     * @throws ReflectionException
     * @covers ::setUpMods
     */
    public function testSetUpMods(): void
    {
        $modNames = ['abc', 'def'];

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
     * @throws ReflectionException
     * @covers ::createDumpInfoJson
     */
    public function testCreateDumpInfoJson(): void
    {
        $baseVersion = '1.2.3';
        $version = '2.3.4';
        $modNames = ['abc', 'def'];

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
                       ->setTitle('Factorio Item Browser - Dump')
                       ->setAuthor('factorio-item-browser')
                       ->setVersion($version)
                       ->setFactorioVersion($baseVersion)
                       ->setDependencies($modNames);

        $instance = new Instance(
            $this->console,
            $this->factorioProcessFactory,
            $this->modFileManager,
            $this->serializer,
            'foo',
            'bar',
            $version
        );

        $result = $this->invokeMethod($instance, 'createDumpInfoJson', $modNames);
        $this->assertEquals($expectedResult, $result);
    }


    /**
     * Tests the createModListJson method.
     * @throws ReflectionException
     * @covers ::createModListJson
     */
    public function testCreateModListJson(): void
    {
        $modNames = ['abc', 'base', 'def'];

        $expectedMod1 = new Mod();
        $expectedMod1->setName('base')
                     ->setEnabled(true);
        $expectedMod2 = new Mod();
        $expectedMod2->setName('Dump')
                     ->setEnabled(true);
        $expectedMod3 = new Mod();
        $expectedMod3->setName('abc')
                     ->setEnabled(true);
        $expectedMod4 = new Mod();
        $expectedMod4->setName('def')
                     ->setEnabled(true);

        $expectedResult = new ModListJson();
        $expectedResult->setMods([$expectedMod1, $expectedMod2, $expectedMod3, $expectedMod4]);

        $instance = new Instance(
            $this->console,
            $this->factorioProcessFactory,
            $this->modFileManager,
            $this->serializer,
            'foo',
            'bar',
            '1.2.3',
        );

        $result = $this->invokeMethod($instance, 'createModListJson', $modNames);

        $this->assertEquals($expectedResult, $result);
    }


    /**
     * Tests the createModListJson method.
     * @throws ReflectionException
     * @covers ::createModListJson
     */
    public function testCreateModListJsonWithoutBase(): void
    {
        $modNames = ['abc', 'def'];

        $expectedMod1 = new Mod();
        $expectedMod1->setName('base')
                     ->setEnabled(false);
        $expectedMod2 = new Mod();
        $expectedMod2->setName('Dump')
                     ->setEnabled(true);
        $expectedMod3 = new Mod();
        $expectedMod3->setName('abc')
                     ->setEnabled(true);
        $expectedMod4 = new Mod();
        $expectedMod4->setName('def')
                     ->setEnabled(true);

        $expectedResult = new ModListJson();
        $expectedResult->setMods([$expectedMod1, $expectedMod2, $expectedMod3, $expectedMod4]);

        $instance = new Instance(
            $this->console,
            $this->factorioProcessFactory,
            $this->modFileManager,
            $this->serializer,
            'foo',
            'bar',
            '1.2.3',
        );

        $result = $this->invokeMethod($instance, 'createModListJson', $modNames);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @throws ReflectionException
     * @covers ::createDirectory
     */
    public function testCreateDirectory(): void
    {
        $directory = vfsStream::setup('root');

        /* @var Instance&MockObject $instance */
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
            $this->factorioProcessFactory,
            $this->modFileManager,
            $this->serializer,
            $factorioDirectory,
            'bar',
            '1.2.3',
        );

        $result = $this->invokeMethod($instance, 'getFactorioPath', $fileName);

        $this->assertSame($expectedResult, $result);
    }

    /**
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
            $this->factorioProcessFactory,
            $this->modFileManager,
            $this->serializer,
            'foo',
            'bar',
            '1.2.3',
        );
        $this->injectProperty($instance, 'combinationInstanceDirectory', $combinationInstanceDirectory);

        $result = $this->invokeMethod($instance, 'getInstancePath', $fileName);

        $this->assertSame($expectedResult, $result);
    }
}
