<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Service;

use BluePsyduck\FactorioModPortalClient\Entity\Dependency;
use BluePsyduck\FactorioModPortalClient\Entity\Version;
use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Export\Entity\InfoJson;
use FactorioItemBrowser\Export\Entity\ModList\Mod;
use FactorioItemBrowser\Export\Entity\ModListJson;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Process\FactorioProcess;
use FactorioItemBrowser\Export\Process\FactorioProcessFactory;
use FactorioItemBrowser\Export\Service\FactorioExecutionService;
use FactorioItemBrowser\Export\Service\ModFileService;
use FactorioItemBrowser\ExportData\ExportData;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * The PHPUnit test of the FactorioExecutionService class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Export\Service\FactorioExecutionService
 */
class FactorioExecutionServiceTest extends TestCase
{
    use ReflectionTrait;

    /** @var SerializerInterface&MockObject */
    private SerializerInterface $exportSerializer;
    /** @var FactorioProcessFactory&MockObject */
    private FactorioProcessFactory $factorioProcessFactory;
    /** @var Filesystem&MockObject */
    private Filesystem $fileSystem;
    /** @var ModFileService&MockObject */
    private ModFileService $modFileService;
    private string $headlessFactorioDirectory = 'foo';
    private string $instancesDirectory = 'bar';
    private string $logsDirectory = 'baz';
    private string $version = '1.2.3';

    protected function setUp(): void
    {
        $this->exportSerializer = $this->createMock(SerializerInterface::class);
        $this->factorioProcessFactory = $this->createMock(FactorioProcessFactory::class);
        $this->fileSystem = $this->createMock(Filesystem::class);
        $this->modFileService = $this->createMock(ModFileService::class);
    }

    /**
     * @param array<string> $mockedMethods
     * @return FactorioExecutionService&MockObject
     */
    private function createInstance(array $mockedMethods = []): FactorioExecutionService
    {
        return $this->getMockBuilder(FactorioExecutionService::class)
                    ->onlyMethods($mockedMethods)
                    ->setConstructorArgs([
                        $this->exportSerializer,
                        $this->factorioProcessFactory,
                        $this->fileSystem,
                        $this->modFileService,
                        $this->headlessFactorioDirectory,
                        $this->instancesDirectory,
                        $this->logsDirectory,
                        $this->version,
                    ])
                    ->getMock();
    }

    /**
     * @throws ExportException
     */
    public function testPrepare(): void
    {
        $combinationId = 'abc';
        $modNames = ['def', 'ghi'];

        $instance = $this->createInstance(['setupInstanceDirectory', 'setupMods', 'setupDumpMod']);
        $instance->expects($this->once())
                 ->method('setupInstanceDirectory')
                 ->with($this->identicalTo($combinationId));
        $instance->expects($this->once())
                 ->method('setupMods')
                 ->with($this->identicalTo($combinationId), $this->identicalTo($modNames));
        $instance->expects($this->once())
                 ->method('setupDumpMod')
                 ->with($this->identicalTo($combinationId), $this->identicalTo($modNames));

        $result = $instance->prepare($combinationId, $modNames);
        $this->assertSame($instance, $result);
    }

    /**
     * @throws ReflectionException
     */
    public function testSetupInstanceDirectory(): void
    {
        $combinationId = 'abc';

        $this->fileSystem->expects($this->once())
                         ->method('remove')
                         ->with($this->identicalTo('bar/abc'));
        $this->fileSystem->expects($this->once())
                         ->method('mkdir')
                         ->with($this->identicalTo('bar/abc/mods'));
        $this->fileSystem->expects($this->exactly(2))
                         ->method('copy')
                         ->withConsecutive(
                             [
                                 $this->identicalTo("foo/bin/x64/factorio"),
                                 $this->identicalTo('bar/abc/bin/x64/factorio'),
                                 $this->isTrue(),
                             ],
                             [
                                 $this->identicalTo('foo/config-path.cfg'),
                                 $this->identicalTo('bar/abc/config-path.cfg'),
                                 $this->isTrue(),
                             ],
                         );
        $this->fileSystem->expects($this->once())
                         ->method('symlink')
                         ->with($this->identicalTo('foo/data'), $this->identicalTo('bar/abc/data'));

        $instance = $this->createInstance();
        $this->invokeMethod($instance, 'setupInstanceDirectory', $combinationId);
    }

    /**
     * @throws ReflectionException
     */
    public function testSetupMods(): void
    {
        $combinationId = 'abc';
        $modNames = ['base', 'def', 'ghi'];
        $modListJson = $this->createMock(ModListJson::class);
        $modListContents = 'jkl';

        $this->modFileService->expects($this->exactly(2))
                             ->method('getLocalDirectory')
                             ->withConsecutive(
                                 [$this->identicalTo('def')],
                                 [$this->identicalTo('ghi')],
                             )
                             ->willReturnOnConsecutiveCalls(
                                 'fed',
                                 'ihg',
                             );

        $this->exportSerializer->expects($this->once())
                               ->method('serialize')
                               ->with($this->identicalTo($modListJson), $this->identicalTo('json'))
                               ->willReturn($modListContents);

        $this->fileSystem->expects($this->exactly(2))
                         ->method('symlink')
                         ->withConsecutive(
                             [$this->identicalTo('fed'), $this->identicalTo('bar/abc/mods/def')],
                             [$this->identicalTo('ihg'), $this->identicalTo('bar/abc/mods/ghi')],
                         );
        $this->fileSystem->expects($this->once())
                         ->method('dumpFile')
                         ->with($this->identicalTo('bar/abc/mods/mod-list.json'), $this->identicalTo($modListContents));

        $instance = $this->createInstance(['createModListJson']);
        $instance->expects($this->once())
                 ->method('createModListJson')
                 ->with($this->identicalTo($modNames))
                 ->willReturn($modListJson);

        $this->invokeMethod($instance, 'setupMods', $combinationId, $modNames);
    }

    /**
     * @throws ReflectionException
     */
    public function testSetupDumpMod(): void
    {
        $combinationId = 'abc';
        $modNames = ['base', 'def', 'ghi'];
        $dumpInfoJson = $this->createMock(InfoJson::class);
        $dumpContents = 'jkl';

        $this->exportSerializer->expects($this->once())
                               ->method('serialize')
                               ->with($this->identicalTo($dumpInfoJson), $this->identicalTo('json'))
                               ->willReturn($dumpContents);

        $this->fileSystem->expects($this->once())
                         ->method('mirror')
                         ->with($this->stringContains('lua/dump'), $this->identicalTo('bar/abc/mods/Dump'));
        $this->fileSystem->expects($this->once())
                         ->method('dumpFile')
                         ->with($this->identicalTo('bar/abc/mods/Dump/info.json'), $this->identicalTo($dumpContents));

        $instance = $this->createInstance(['createDumpInfoJson']);
        $instance->expects($this->once())
                 ->method('createDumpInfoJson')
                 ->with($this->identicalTo($modNames))
                 ->willReturn($dumpInfoJson);

        $this->invokeMethod($instance, 'setupDumpMod', $combinationId, $modNames);
    }

    /**
     * @throws ReflectionException
     */
    public function testCreateDumpInfoJson(): void
    {
        $modNames = ['base', 'abc', 'def'];
        $baseInfo = new InfoJson();
        $baseInfo->version = new Version('2.3.4');

        $expectedResult = new InfoJson();
        $expectedResult->name = 'Dump';
        $expectedResult->title = 'Factorio Item Browser - Dump';
        $expectedResult->author = 'factorio-item-browser';
        $expectedResult->version = new Version($this->version);
        $expectedResult->factorioVersion = new Version('2.3.4');
        $expectedResult->dependencies = [
            new Dependency('base'),
            new Dependency('abc'),
            new Dependency('def'),
        ];

        $this->modFileService->expects($this->once())
                             ->method('getInfo')
                             ->with($this->identicalTo('base'))
                             ->willReturn($baseInfo);

        $instance = $this->createInstance();
        $result = $this->invokeMethod($instance, 'createDumpInfoJson', $modNames);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @throws ReflectionException
     */
    public function testCreateModListJson(): void
    {
        $modNames = ['base', 'abc', 'def'];

        $mod1 = new Mod();
        $mod1->name = 'base';
        $mod1->isEnabled = true;

        $mod2 = new Mod();
        $mod2->name = 'Dump';
        $mod2->isEnabled = true;

        $mod3 = new Mod();
        $mod3->name = 'abc';
        $mod3->isEnabled = true;

        $mod4 = new Mod();
        $mod4->name = 'def';
        $mod4->isEnabled = true;

        $expectedResult = new ModListJson();
        $expectedResult->mods = [$mod1, $mod2, $mod3, $mod4];

        $instance = $this->createInstance();
        $result = $this->invokeMethod($instance, 'createModListJson', $modNames);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @throws ReflectionException
     */
    public function testCreateModListJsonWithoutBaseMod(): void
    {
        $modNames = ['abc', 'def'];

        $mod1 = new Mod();
        $mod1->name = 'base';
        $mod1->isEnabled = false;

        $mod2 = new Mod();
        $mod2->name = 'Dump';
        $mod2->isEnabled = true;

        $mod3 = new Mod();
        $mod3->name = 'abc';
        $mod3->isEnabled = true;

        $mod4 = new Mod();
        $mod4->name = 'def';
        $mod4->isEnabled = true;

        $expectedResult = new ModListJson();
        $expectedResult->mods = [$mod1, $mod2, $mod3, $mod4];

        $instance = $this->createInstance();
        $result = $this->invokeMethod($instance, 'createModListJson', $modNames);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @throws ExportException
     */
    public function testExecute(): void
    {
        $combinationId = 'abc';
        $exportData = $this->createMock(ExportData::class);

        $process = $this->createMock(FactorioProcess::class);
        $process->expects($this->once())
                ->method('run');

        $this->factorioProcessFactory->expects($this->once())
                                     ->method('create')
                                     ->with($this->identicalTo($exportData), $this->identicalTo('bar/abc'))
                                     ->willReturn($process);

        $instance = $this->createInstance();
        $instance->execute($exportData, $combinationId);
    }

    public function testCleanup(): void
    {
        $combinationId = 'abc';

        $this->fileSystem->expects($this->once())
                         ->method('copy')
                         ->with(
                             $this->identicalTo('bar/abc/factorio-current.log'),
                             $this->identicalTo('baz/factorio_abc.log'),
                         );

        $this->fileSystem->expects($this->once())
                         ->method('remove')
                         ->with($this->identicalTo('bar/abc'));

        $instance = $this->createInstance();
        $result = $instance->cleanup($combinationId);

        $this->assertSame($instance, $result);
    }
}
