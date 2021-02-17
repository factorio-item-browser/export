<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Process;

use BluePsyduck\FactorioModPortalClient\Entity\Mod;
use BluePsyduck\FactorioModPortalClient\Entity\Release;
use BluePsyduck\FactorioModPortalClient\Entity\Version;
use BluePsyduck\SymfonyProcessManager\ProcessManager;
use BluePsyduck\SymfonyProcessManager\ProcessManagerInterface;
use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Export\Exception\DownloadFailedException;
use FactorioItemBrowser\Export\Output\Console;
use FactorioItemBrowser\Export\Output\ProgressBar;
use FactorioItemBrowser\Export\Process\ModDownloadProcess;
use FactorioItemBrowser\Export\Process\ModDownloadProcessFactory;
use FactorioItemBrowser\Export\Process\ModDownloadProcessManager;
use FactorioItemBrowser\Export\Service\ModFileService;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use ReflectionException;

/**
 * The PHPUnit test of the ModDownloadProcessManager class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Export\Process\ModDownloadProcessManager
 */
class ModDownloadProcessManagerTest extends TestCase
{
    use ReflectionTrait;

    /** @var Console&MockObject */
    private Console $console;
    /** @var LoggerInterface&MockObject */
    private LoggerInterface $logger;
    /** @var ModDownloadProcessFactory&MockObject */
    private ModDownloadProcessFactory $modDownloadProcessFactory;
    /** @var ModFileService&MockObject */
    private ModFileService $modFileService;
    private int $numberOfParallelDownloads;

    /** @var ProcessManagerInterface&MockObject */
    private ProcessManagerInterface $processManager;
    /** @var ProgressBar&MockObject */
    private ProgressBar $progressBar;

    protected function setUp(): void
    {
        $this->console = $this->createMock(Console::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->modDownloadProcessFactory = $this->createMock(ModDownloadProcessFactory::class);
        $this->modFileService = $this->createMock(ModFileService::class);
        $this->numberOfParallelDownloads = 42;

        $this->processManager = $this->createMock(ProcessManagerInterface::class);
        $this->progressBar = $this->createMock(ProgressBar::class);
    }

    /**
     * @param array<string> $mockedMethods
     * @param bool $mockInstances
     * @return ModDownloadProcessManager&MockObject
     */
    private function createInstance(array $mockedMethods = [], bool $mockInstances = true): ModDownloadProcessManager
    {
        if ($mockInstances) {
            $mockedMethods[] = 'getProcessManager';
            $mockedMethods[] = 'getProgressBar';
        }

        $instance = $this->getMockBuilder(ModDownloadProcessManager::class)
                         ->disableProxyingToOriginalMethods()
                         ->onlyMethods($mockedMethods)
                         ->setConstructorArgs([
                             $this->console,
                             $this->logger,
                             $this->modDownloadProcessFactory,
                             $this->modFileService,
                             $this->numberOfParallelDownloads,
                         ])
                         ->getMock();

        if ($mockInstances) {
            $instance->expects($this->any())
                     ->method('getProcessManager')
                     ->willReturn($this->processManager);
            $instance->expects($this->any())
                     ->method('getProgressBar')
                     ->willReturn($this->progressBar);
        }

        return $instance;
    }

    /**
     * @throws ReflectionException
     */
    public function testGetProcessManager(): void
    {
        $process = $this->createMock(ModDownloadProcess::class);

        $instance = $this->createInstance(['handleProcessStart', 'handleProcessFinish'], false);
        $instance->expects($this->once())
                 ->method('handleProcessStart')
                 ->with($this->identicalTo($process));
        $instance->expects($this->once())
                 ->method('handleProcessFinish')
                 ->with($this->identicalTo($process));

        $result1 = $this->invokeMethod($instance, 'getProcessManager');
        $result2 = $this->invokeMethod($instance, 'getProcessManager');
        $this->assertInstanceOf(ProcessManager::class, $result1);
        $this->assertSame($result1, $result2);

        $this->assertSame(42, $this->extractProperty($result1, 'numberOfParallelProcesses'));

        $this->extractProperty($result1, 'processStartCallback')($process);
        $this->extractProperty($result1, 'processFinishCallback')($process);
    }

    /**
     * @throws ReflectionException
     */
    public function testGetProgressBar(): void
    {
        $progressBar = $this->createMock(ProgressBar::class);

        $this->console->expects($this->once())
                      ->method('createProgressBar')
                      ->with($this->isType('string'))
                      ->willReturn($progressBar);

        $instance = $this->createInstance([], false);

        $result1 = $this->invokeMethod($instance, 'getProgressBar');
        $result2 = $this->invokeMethod($instance, 'getProgressBar');
        $this->assertSame($progressBar, $result1);
        $this->assertSame($progressBar, $result2);
    }

    public function testAdd(): void
    {
        $mod = $this->createMock(Mod::class);
        $release = $this->createMock(Release::class);
        $process = $this->createMock(ModDownloadProcess::class);

        $this->modDownloadProcessFactory->expects($this->once())
                                        ->method('create')
                                        ->with($this->identicalTo($mod), $this->identicalTo($release))
                                        ->willReturn($process);

        $this->progressBar->expects($this->once())
                          ->method('getNumberOfSteps')
                          ->willReturn(21);
        $this->progressBar->expects($this->once())
                          ->method('setNumberOfSteps')
                          ->with($this->identicalTo(22));

        $this->processManager->expects($this->once())
                             ->method('addProcess')
                             ->with($this->identicalTo($process));

        $instance = $this->createInstance();
        $instance->add($mod, $release);
    }

    public function testWait(): void
    {
        $this->processManager->expects($this->once())
                             ->method('waitForAllProcesses');

        $instance = $this->createInstance();
        $instance->wait();
    }

    /**
     * @throws ReflectionException
     */
    public function testHandleProcessStart(): void
    {
        $mod = new Mod();
        $mod->setName('abc');
        $release = new Release();
        $release->setVersion(new Version('1.2.3'));

        $process = $this->createMock(ModDownloadProcess::class);
        $process->expects($this->any())
                ->method('getMod')
                ->willReturn($mod);
        $process->expects($this->any())
                ->method('getRelease')
                ->willReturn($release);

        $this->logger->expects($this->once())
                     ->method('info')
                     ->with($this->isType('string'), $this->identicalTo(['mod' => 'abc', 'version' => '1.2.3']));

        $this->progressBar->expects($this->once())
                          ->method('start')
                          ->with($this->identicalTo('abc'), $this->isType('string'));

        $instance = $this->createInstance();
        $this->invokeMethod($instance, 'handleProcessStart', $process);
    }

    /**
     * @throws ReflectionException
     */
    public function testHandleProcessFinish(): void
    {
        vfsStream::setup('root');
        $destinationFile = vfsStream::url('root/temp-file');
        file_put_contents($destinationFile, 'foo');

        $mod = new Mod();
        $mod->setName('abc');
        $release = new Release();
        $release->setVersion(new Version('1.2.3'))
                ->setSha1('0beec7b5ea3f0fdbc95d0dd47f3c5bc275da8a33');

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

        $this->logger->expects($this->once())
                     ->method('info')
                     ->with($this->isType('string'), $this->identicalTo(['mod' => 'abc', 'version' => '1.2.3']));

        $this->progressBar->expects($this->once())
                          ->method('update')
                          ->with($this->identicalTo('abc'), $this->isType('string'));
        $this->progressBar->expects($this->once())
                          ->method('finish')
                          ->with($this->identicalTo('abc'));

        $this->modFileService->expects($this->once())
                             ->method('addModArchive')
                             ->with($this->identicalTo('abc'), $this->identicalTo($destinationFile));

        $instance = $this->createInstance();
        $this->invokeMethod($instance, 'handleProcessFinish', $process);

        $this->assertFalse(file_exists($destinationFile));
    }

    /**
     * @throws ReflectionException
     */
    public function testHandleProcessFinishWithFailedProcess(): void
    {
        $process = $this->createMock(ModDownloadProcess::class);
        $process->expects($this->any())
                ->method('isSuccessful')
                ->willReturn(false);

        $this->modFileService->expects($this->never())
                             ->method('addModArchive');

        $this->expectException(DownloadFailedException::class);

        $instance = $this->createInstance();
        $this->invokeMethod($instance, 'handleProcessFinish', $process);
    }

    /**
     * @throws ReflectionException
     */
    public function testHandleProcessFinishWithHashMismatch(): void
    {
        vfsStream::setup('root');
        $destinationFile = vfsStream::url('root/temp-file');
        file_put_contents($destinationFile, 'foo');

        $mod = new Mod();
        $mod->setName('abc');
        $release = new Release();
        $release->setVersion(new Version('1.2.3'))
                ->setSha1('bar');

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

        $this->modFileService->expects($this->never())
                             ->method('addModArchive');

        $this->expectException(DownloadFailedException::class);

        $instance = $this->createInstance();
        $this->invokeMethod($instance, 'handleProcessFinish', $process);

        $this->assertFalse(file_exists($destinationFile));
    }
}
