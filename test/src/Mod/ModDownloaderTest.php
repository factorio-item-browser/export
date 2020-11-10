<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Mod;

use BluePsyduck\FactorioModPortalClient\Client\Facade;
use BluePsyduck\FactorioModPortalClient\Entity\Mod;
use BluePsyduck\FactorioModPortalClient\Entity\Release;
use BluePsyduck\FactorioModPortalClient\Entity\Version;
use BluePsyduck\FactorioModPortalClient\Exception\ClientException;
use BluePsyduck\FactorioModPortalClient\Request\ModListRequest;
use BluePsyduck\FactorioModPortalClient\Response\ModListResponse;
use BluePsyduck\SymfonyProcessManager\ProcessManager;
use BluePsyduck\SymfonyProcessManager\ProcessManagerInterface;
use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Common\Constant\Constant;
use FactorioItemBrowser\Export\Console\Console;
use FactorioItemBrowser\Export\Entity\InfoJson;
use FactorioItemBrowser\Export\Exception\DownloadFailedException;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Exception\InternalException;
use FactorioItemBrowser\Export\Exception\MissingModException;
use FactorioItemBrowser\Export\Exception\NoValidReleaseException;
use FactorioItemBrowser\Export\Mod\ModDownloader;
use FactorioItemBrowser\Export\Mod\ModFileManager;
use FactorioItemBrowser\Export\Process\ModDownloadProcess;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the ModDownloader class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Mod\ModDownloader
 */
class ModDownloaderTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked console.
     * @var Console&MockObject
     */
    protected $console;

    /**
     * The mocked mod file manager.
     * @var ModFileManager&MockObject
     */
    protected $modFileManager;

    /**
     * The mocked mod portal client facade.
     * @var Facade&MockObject
     */
    protected $modPortalClientFacade;

    /**
     * Sets up the test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->console = $this->createMock(Console::class);
        $this->modFileManager = $this->createMock(ModFileManager::class);
        $this->modPortalClientFacade = $this->createMock(Facade::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $numberOfParallelDownloads = 42;
        $tempDirectory = 'abc';

        $downloader = new ModDownloader(
            $this->console,
            $this->modFileManager,
            $this->modPortalClientFacade,
            $numberOfParallelDownloads,
            $tempDirectory
        );

        $this->assertSame($this->console, $this->extractProperty($downloader, 'console'));
        $this->assertSame($this->modFileManager, $this->extractProperty($downloader, 'modFileManager'));
        $this->assertSame($this->modPortalClientFacade, $this->extractProperty($downloader, 'modPortalClientFacade'));
        $this->assertSame($numberOfParallelDownloads, $this->extractProperty($downloader, 'numberOfParallelDownloads'));
        $this->assertSame($tempDirectory, $this->extractProperty($downloader, 'tempDirectory'));
    }

    /**
     * Tests the download method.
     * @throws ExportException
     * @covers ::download
     */
    public function testDownload(): void
    {
        $modNames = ['abc', 'def', 'ghi'];

        $mod1 = new Mod();
        $mod1->setName('abc');
        $mod2 = new Mod();
        $mod2->setName('def');
        $mod3 = new Mod();
        $mod3->setName('ghi');

        $mods = [$mod1, $mod2, $mod3];

        /* @var Release&MockObject $release1 */
        $release1 = $this->createMock(Release::class);
        /* @var Release&MockObject $release2 */
        $release2 = $this->createMock(Release::class);

        /* @var ModDownloadProcess&MockObject $process1 */
        $process1 = $this->createMock(ModDownloadProcess::class);
        /* @var ModDownloadProcess&MockObject $process2 */
        $process2 = $this->createMock(ModDownloadProcess::class);

        /* @var ProcessManagerInterface&MockObject $processManager */
        $processManager = $this->createMock(ProcessManagerInterface::class);
        $processManager->expects($this->exactly(2))
                       ->method('addProcess')
                       ->withConsecutive(
                           [$this->identicalTo($process1)],
                           [$this->identicalTo($process2)]
                       );
        $processManager->expects($this->once())
                       ->method('waitForAllProcesses');

        $this->console->expects($this->once())
                      ->method('writeAction')
                      ->with($this->identicalTo('Loading meta information from the Mod Portal'));
        $this->console->expects($this->once())
                      ->method('writeMessage')
                      ->with($this->identicalTo('Mod def is already up-to-date.'));

        /* @var ModDownloader&MockObject $downloader */
        $downloader = $this->getMockBuilder(ModDownloader::class)
                           ->onlyMethods([
                               'fetchMetaData',
                               'verifyMods',
                               'createProcessManager',
                               'getReleaseToDownload',
                               'createDownloadProcess',
                           ])
                           ->setConstructorArgs([
                               $this->console,
                               $this->modFileManager,
                               $this->modPortalClientFacade,
                               42,
                               'foo',
                           ])
                           ->getMock();
        $downloader->expects($this->once())
                   ->method('fetchMetaData')
                   ->with($this->identicalTo($modNames))
                   ->willReturn($mods);
        $downloader->expects($this->once())
                   ->method('verifyMods')
                   ->with($this->identicalTo($modNames), $this->identicalTo($mods));
        $downloader->expects($this->once())
                   ->method('createProcessManager')
                   ->willReturn($processManager);
        $downloader->expects($this->exactly(3))
                   ->method('getReleaseToDownload')
                   ->withConsecutive(
                       [$this->identicalTo($mod1)],
                       [$this->identicalTo($mod2)],
                       [$this->identicalTo($mod3)]
                   )
                   ->willReturnOnConsecutiveCalls(
                       $release1,
                       null,
                       $release2
                   );
        $downloader->expects($this->exactly(2))
                   ->method('createDownloadProcess')
                   ->withConsecutive(
                       [$this->identicalTo($mod1), $this->identicalTo($release1)],
                       [$this->identicalTo($mod3), $this->identicalTo($release2)]
                   )
                   ->willReturnOnConsecutiveCalls(
                       $process1,
                       $process2
                   );

        $downloader->download($modNames);
    }

    /**
     * Tests the fetchMetaData method.
     * @throws ReflectionException
     * @covers ::fetchMetaData
     */
    public function testFetchMetaData(): void
    {
        $modNames = ['abc', 'def'];

        $expectedRequest = new ModListRequest();
        $expectedRequest->setNameList($modNames)
                        ->setPageSize(2);

        $mod1 = new Mod();
        $mod1->setName('abc');
        $mod2 = new Mod();
        $mod2->setName('def');

        $expectedResult = [
            'abc' => $mod1,
            'def' => $mod2,
        ];

        /* @var ModListResponse&MockObject $response */
        $response = $this->createMock(ModListResponse::class);
        $response->expects($this->once())
                 ->method('getResults')
                 ->willReturn([$mod1, $mod2]);

        $this->modPortalClientFacade->expects($this->once())
                                    ->method('getModList')
                                    ->with($this->equalTo($expectedRequest))
                                    ->willReturn($response);

        $downloader = new ModDownloader($this->console, $this->modFileManager, $this->modPortalClientFacade, 42, 'foo');
        $result = $this->invokeMethod($downloader, 'fetchMetaData', $modNames);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the fetchMetaData method.
     * @throws ReflectionException
     * @covers ::fetchMetaData
     */
    public function testFetchMetaDataWithException(): void
    {
        $modNames = ['abc', 'def'];

        $expectedRequest = new ModListRequest();
        $expectedRequest->setNameList($modNames)
                        ->setPageSize(2);

        /* @var ModListResponse&MockObject $response */
        $response = $this->createMock(ModListResponse::class);
        $response->expects($this->once())
                 ->method('getResults')
                 ->willThrowException($this->createMock(ClientException::class));

        $this->modPortalClientFacade->expects($this->once())
                                    ->method('getModList')
                                    ->with($this->equalTo($expectedRequest))
                                    ->willReturn($response);

        $this->expectException(InternalException::class);

        $downloader = new ModDownloader($this->console, $this->modFileManager, $this->modPortalClientFacade, 42, 'foo');
        $this->invokeMethod($downloader, 'fetchMetaData', $modNames);
    }

    /**
     * Provides the data for the verifyMods test.
     * @return array<mixed>
     */
    public function provideVerifyMods(): array
    {
        /* @var Mod&MockObject $mod1 */
        $mod1 = $this->createMock(Mod::class);
        /* @var Mod&MockObject $mod2 */
        $mod2 = $this->createMock(Mod::class);

        return [
            [['abc', 'base', 'def'], ['abc' => $mod1, 'def' => $mod2], false],
            [['abc', 'base', 'def'], ['abc' => $mod1], true],
        ];
    }

    /**
     * Tests the verifyMods method.
     * @param array|string[] $modNames
     * @param array|Mod[] $mods
     * @param bool $expectException
     * @throws ReflectionException
     * @covers ::verifyMods
     * @dataProvider provideVerifyMods
     */
    public function testVerifyMods(array $modNames, array $mods, bool $expectException): void
    {
        if ($expectException) {
            $this->expectException(MissingModException::class);
        } else {
            $this->addToAssertionCount(1);
        }

        $downloader = new ModDownloader($this->console, $this->modFileManager, $this->modPortalClientFacade, 42, 'foo');
        $this->invokeMethod($downloader, 'verifyMods', $modNames, $mods);
    }

    /**
     * Provides the data for the getReleaseToDownload test.
     * @return array<mixed>
     */
    public function provideGetReleaseToDownload(): array
    {
        $release = new Release();
        $release->setVersion(new Version('1.2.3'));

        return [
            ['1.2.2', $release, $release], // Update to newer version
            ['1.2.3', $release, null], // Local version already up-to-date
            [null, $release, $release], // No local release
        ];
    }

    /**
     * Tests the getReleaseToDownload method.
     * @param string|null $currentVersion
     * @param Release $latestRelease
     * @param Release|null $expectedResult
     * @throws ReflectionException
     * @covers ::getReleaseToDownload
     * @dataProvider provideGetReleaseToDownload
     */
    public function testGetReleaseToDownload(
        ?string $currentVersion,
        Release $latestRelease,
        ?Release $expectedResult
    ): void {
        $modName = 'abc';

        $mod = new Mod();
        $mod->setName($modName);

        if ($currentVersion === null) {
            $this->modFileManager->expects($this->once())
                                 ->method('getInfo')
                                 ->with($this->identicalTo($modName))
                                 ->willThrowException($this->createMock(ExportException::class));
        } else {
            $info = new InfoJson();
            $info->setVersion($currentVersion);

            $this->modFileManager->expects($this->once())
                                 ->method('getInfo')
                                 ->with($this->identicalTo($modName))
                                 ->willReturn($info);
        }

        /* @var ModDownloader&MockObject $downloader */
        $downloader = $this->getMockBuilder(ModDownloader::class)
                           ->onlyMethods(['findLatestRelease'])
                           ->setConstructorArgs([
                               $this->console,
                               $this->modFileManager,
                               $this->modPortalClientFacade,
                               42,
                               'foo',
                           ])
                           ->getMock();
        $downloader->expects($this->once())
                   ->method('findLatestRelease')
                   ->with($this->identicalTo($mod))
                   ->willReturn($latestRelease);

        $result = $this->invokeMethod($downloader, 'getReleaseToDownload', $mod);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the findLatestRelease method.
     * @throws ReflectionException
     * @covers ::findLatestRelease
     */
    public function testFindLatestRelease(): void
    {
        $factorioVersion = new Version('4.2.0');

        $release = new Release();
        $release->getInfoJson()->setFactorioVersion($factorioVersion);

        $mod = new Mod();
        $mod->setReleases([$release]);

        $downloader = $this->getMockBuilder(ModDownloader::class)
                           ->onlyMethods(['getFactorioVersion'])
                           ->setConstructorArgs([
                               $this->console,
                               $this->modFileManager,
                               $this->modPortalClientFacade,
                               42,
                               'foo',
                           ])
                           ->getMock();
        $downloader->expects($this->any())
                   ->method('getFactorioVersion')
                   ->willReturn($factorioVersion);

        $result = $this->invokeMethod($downloader, 'findLatestRelease', $mod);

        $this->assertSame($release, $result);
    }

    /**
     * Tests the findLatestRelease method.
     * @throws ReflectionException
     * @covers ::findLatestRelease
     */
    public function testFindLatestReleaseWithException(): void
    {
        $mod = new Mod();

        $this->expectException(NoValidReleaseException::class);

        $downloader = new ModDownloader($this->console, $this->modFileManager, $this->modPortalClientFacade, 42, 'foo');
        $this->invokeMethod($downloader, 'findLatestRelease', $mod);
    }

    /**
     * Tests the getFactorioVersion method.
     * @throws ReflectionException
     * @covers ::getFactorioVersion
     */
    public function testGetFactorioVersion(): void
    {
        $baseVersion = '1.2.3';
        $expectedVersion = new Version('1.2.3');

        $baseInfo = new InfoJson();
        $baseInfo->setVersion($baseVersion);

        $this->modFileManager->expects($this->once())
                             ->method('getInfo')
                             ->with($this->identicalTo(Constant::MOD_NAME_BASE))
                             ->willReturn($baseInfo);

        $downloader = new ModDownloader($this->console, $this->modFileManager, $this->modPortalClientFacade, 42, 'foo');
        $this->injectProperty($downloader, 'factorioVersion', null);

        $result = $this->invokeMethod($downloader, 'getFactorioVersion');
        $this->assertEquals($expectedVersion, $result);
        $this->assertEquals($expectedVersion, $this->extractProperty($downloader, 'factorioVersion'));
    }

    /**
     * Tests the getFactorioVersion method.
     * @throws ReflectionException
     * @covers ::getFactorioVersion
     */
    public function testGetFactorioVersionWithAvailableVersion(): void
    {
        $factorioVersion = $this->createMock(Version::class);

        $this->modFileManager->expects($this->never())
                             ->method('getInfo');

        $downloader = new ModDownloader($this->console, $this->modFileManager, $this->modPortalClientFacade, 42, 'foo');
        $this->injectProperty($downloader, 'factorioVersion', $factorioVersion);

        $result = $this->invokeMethod($downloader, 'getFactorioVersion');
        $this->assertSame($factorioVersion, $result);
        $this->assertSame($factorioVersion, $this->extractProperty($downloader, 'factorioVersion'));
    }

    /**
     * Tests the createProcessManager method.
     * @throws ReflectionException
     * @covers ::createProcessManager
     */
    public function testCreateProcessManager(): void
    {
        $numberOfParallelDownloads = 42;

        /* @var ModDownloadProcess&MockObject $process */
        $process = $this->createMock(ModDownloadProcess::class);

        /* @var ModDownloader&MockObject $downloader */
        $downloader = $this->getMockBuilder(ModDownloader::class)
                           ->onlyMethods(['handleProcessStart', 'handleProcessFinish'])
                           ->setConstructorArgs([
                               $this->console,
                               $this->modFileManager,
                               $this->modPortalClientFacade,
                               $numberOfParallelDownloads,
                               'foo',
                           ])
                           ->getMock();
        $downloader->expects($this->once())
                   ->method('handleProcessStart')
                   ->with($this->identicalTo($process));
        $downloader->expects($this->once())
                   ->method('handleProcessFinish')
                   ->with($this->identicalTo($process));

        /* @var ProcessManager $result */
        $result = $this->invokeMethod($downloader, 'createProcessManager');
        $this->assertSame($numberOfParallelDownloads, $this->extractProperty($result, 'numberOfParallelProcesses'));

        $startCallback = $this->extractProperty($result, 'processStartCallback');
        $this->assertIsCallable($startCallback);
        $startCallback($process);

        $finishCallback = $this->extractProperty($result, 'processFinishCallback');
        $this->assertIsCallable($finishCallback);
        $finishCallback($process);
    }

    /**
     * Tests the createDownloadProcess method.
     * @throws ReflectionException
     * @covers ::createDownloadProcess
     */
    public function testCreateDownloadProcess(): void
    {
        $tempDirectory = 'abc';
        $downloadUrl = 'def';
        $fileName = 'ghi';
        $fullDownloadUrl = 'jkl';
        $expectedFileName = 'abc/ghi';

        $release = new Release();
        $release->setDownloadUrl($downloadUrl)
                ->setFileName($fileName);

        /* @var Mod&MockObject $mod */
        $mod = $this->createMock(Mod::class);

        $expectedResult = new ModDownloadProcess($mod, $release, $fullDownloadUrl, $expectedFileName);

        $this->modPortalClientFacade->expects($this->once())
                                    ->method('getDownloadUrl')
                                    ->with($this->identicalTo($downloadUrl))
                                    ->willReturn($fullDownloadUrl);

        $downloader = new ModDownloader(
            $this->console,
            $this->modFileManager,
            $this->modPortalClientFacade,
            42,
            $tempDirectory
        );
        $result = $this->invokeMethod($downloader, 'createDownloadProcess', $mod, $release);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the handleProcessStart method.
     * @throws ReflectionException
     * @covers ::handleProcessStart
     */
    public function testHandleProcessStart(): void
    {
        $modName = 'abc';
        $releaseVersion = new Version('1.2.3');

        $mod = new Mod();
        $mod->setName($modName);

        $release = new Release();
        $release->setVersion($releaseVersion);

        /* @var ModDownloadProcess&MockObject $process */
        $process = $this->createMock(ModDownloadProcess::class);
        $process->expects($this->once())
                ->method('getMod')
                ->willReturn($mod);
        $process->expects($this->once())
                ->method('getRelease')
                ->willReturn($release);

        $this->console->expects($this->once())
                      ->method('writeAction')
                      ->with($this->identicalTo('Downloading abc (1.2.3)'));

        $downloader = new ModDownloader($this->console, $this->modFileManager, $this->modPortalClientFacade, 42, 'foo');
        $this->invokeMethod($downloader, 'handleProcessStart', $process);
    }

    /**
     * Tests the handleProcessFinish method.
     * @throws ReflectionException
     * @covers ::handleProcessFinish
     */
    public function testHandleProcessFinish(): void
    {
        $directory = vfsStream::setup('root');
        $destinationFile = vfsStream::url('root/test.zip');
        file_put_contents($destinationFile, 'abc');

        $modName = 'def';
        $mod = new Mod();
        $mod->setName($modName);

        $release = new Release();
        $release->setSha1(sha1('abc'));

        /* @var ModDownloadProcess&MockObject $process */
        $process = $this->createMock(ModDownloadProcess::class);
        $process->expects($this->any())
                ->method('isSuccessful')
                ->willReturn(true);
        $process->expects($this->any())
                ->method('getMod')
                ->willReturn($mod);
        $process->expects($this->any())
                ->method('getRelease')
                ->willReturn($release);
        $process->expects($this->any())
                ->method('getDestinationFile')
                ->willReturn($destinationFile);

        $this->console->expects($this->once())
                      ->method('writeAction')
                      ->with($this->identicalTo('Extracting def'));

        $this->modFileManager->expects($this->once())
                             ->method('extractModZip')
                             ->with($this->identicalTo($modName), $this->identicalTo($destinationFile));

        $this->assertTrue($directory->hasChild('test.zip'));

        $downloader = new ModDownloader($this->console, $this->modFileManager, $this->modPortalClientFacade, 42, 'foo');
        $this->invokeMethod($downloader, 'handleProcessFinish', $process);

        $this->assertFalse($directory->hasChild('test.zip'));
    }

    /**
     * Tests the handleProcessFinish method.
     * @throws ReflectionException
     * @covers ::handleProcessFinish
     */
    public function testHandleProcessFinishWithFailedProcess(): void
    {
        /* @var ModDownloadProcess&MockObject $process */
        $process = $this->createMock(ModDownloadProcess::class);
        $process->expects($this->any())
                ->method('isSuccessful')
                ->willReturn(false);

        $this->console->expects($this->never())
                      ->method('writeAction');

        $this->modFileManager->expects($this->never())
                             ->method('extractModZip');

        $this->expectException(DownloadFailedException::class);

        $downloader = new ModDownloader($this->console, $this->modFileManager, $this->modPortalClientFacade, 42, 'foo');
        $this->invokeMethod($downloader, 'handleProcessFinish', $process);
    }

    /**
     * Tests the handleProcessFinish method.
     * @throws ReflectionException
     * @covers ::handleProcessFinish
     */
    public function testHandleProcessFinishWithInvalidSha1Hash(): void
    {
        $directory = vfsStream::setup('root');
        $destinationFile = vfsStream::url('root/test.zip');
        file_put_contents($destinationFile, 'abc');

        $modName = 'def';
        $mod = new Mod();
        $mod->setName($modName);

        $release = new Release();
        $release->setSha1(sha1('fail'));

        /* @var ModDownloadProcess&MockObject $process */
        $process = $this->createMock(ModDownloadProcess::class);
        $process->expects($this->any())
                ->method('isSuccessful')
                ->willReturn(true);
        $process->expects($this->any())
                ->method('getMod')
                ->willReturn($mod);
        $process->expects($this->any())
                ->method('getRelease')
                ->willReturn($release);
        $process->expects($this->any())
                ->method('getDestinationFile')
                ->willReturn($destinationFile);

        $this->console->expects($this->never())
                      ->method('writeAction');

        $this->modFileManager->expects($this->never())
                             ->method('extractModZip');

        $this->assertTrue($directory->hasChild('test.zip'));
        $this->expectException(DownloadFailedException::class);

        $downloader = new ModDownloader($this->console, $this->modFileManager, $this->modPortalClientFacade, 42, 'foo');
        $this->invokeMethod($downloader, 'handleProcessFinish', $process);

        $this->assertFalse($directory->hasChild('test.zip'));
    }
}
