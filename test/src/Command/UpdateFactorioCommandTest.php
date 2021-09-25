<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Command;

use BluePsyduck\FactorioModPortalClient\Entity\Version;
use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\CombinationApi\Client\ClientInterface;
use FactorioItemBrowser\CombinationApi\Client\Constant\JobPriority;
use FactorioItemBrowser\CombinationApi\Client\Exception\ClientException;
use FactorioItemBrowser\CombinationApi\Client\Request\Job\CreateRequest;
use FactorioItemBrowser\Common\Constant\Constant;
use FactorioItemBrowser\Common\Constant\Defaults;
use FactorioItemBrowser\Export\Command\UpdateFactorioCommand;
use FactorioItemBrowser\Export\Constant\CommandName;
use FactorioItemBrowser\Export\Entity\InfoJson;
use FactorioItemBrowser\Export\Exception\CommandException;
use FactorioItemBrowser\Export\Output\Console;
use FactorioItemBrowser\Export\Process\CommandProcess;
use FactorioItemBrowser\Export\Service\FactorioDownloadService;
use FactorioItemBrowser\Export\Service\ModFileService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The PHPUnit test of the UpdateFactorioCommand class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Export\Command\UpdateFactorioCommand
 */
class UpdateFactorioCommandTest extends TestCase
{
    use ReflectionTrait;

    /** @var ClientInterface&MockObject */
    private ClientInterface $combinationApiClient;
    /** @var Console&MockObject */
    private Console $console;
    /** @var FactorioDownloadService&MockObject */
    private FactorioDownloadService $factorioDownloadService;
    /** @var ModFileService&MockObject */
    private ModFileService $modFileService;

    protected function setUp(): void
    {
        $this->combinationApiClient = $this->createMock(ClientInterface::class);
        $this->console = $this->createMock(Console::class);
        $this->factorioDownloadService = $this->createMock(FactorioDownloadService::class);
        $this->modFileService = $this->createMock(ModFileService::class);
    }

    /**
     * @param array<string> $mockedMethods
     * @return UpdateFactorioCommand&MockObject
     */
    private function createInstance(array $mockedMethods = []): UpdateFactorioCommand
    {
        return $this->getMockBuilder(UpdateFactorioCommand::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($mockedMethods)
                    ->setConstructorArgs([
                        $this->combinationApiClient,
                        $this->console,
                        $this->factorioDownloadService,
                        $this->modFileService,
                    ])
                    ->getMock();
    }

    /**
     * @throws ReflectionException
     */
    public function testConfigure(): void
    {
        $instance = $this->createInstance(['setName', 'setDescription']);
        $instance->expects($this->once())
                 ->method('setName')
                 ->with($this->identicalTo(CommandName::UPDATE_FACTORIO));
        $instance->expects($this->once())
                 ->method('setDescription')
                 ->with($this->isType('string'));

        $this->invokeMethod($instance, 'configure');
    }

    /**
     * @throws ReflectionException
     */
    public function testExecute(): void
    {
        $version = '1.2.3';
        $latestVersion = '2.3.4';
        $exitCodeDownload = 0;
        $expectedResult = 0;

        $baseInfo = new InfoJson();
        $baseInfo->version = new Version($version);
        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);

        $downloadProcess = $this->createMock(CommandProcess::class);
        $downloadProcess->expects($this->once())
                        ->method('run')
                        ->with($this->callback(function ($callback): bool {
                            $this->assertIsCallable($callback);
                            $callback('foo', 'bar');
                            return true;
                        }));
        $downloadProcess->expects($this->once())
                        ->method('getExitCode')
                        ->willReturn($exitCodeDownload);

        $this->modFileService->expects($this->once())
                             ->method('getInfo')
                             ->with($this->identicalTo(Constant::MOD_NAME_BASE))
                             ->willReturn($baseInfo);

        $this->factorioDownloadService->expects($this->once())
                                      ->method('getLatestVersion')
                                      ->willReturn($latestVersion);

        $instance = $this->createInstance(['createDownloadProcess', 'createExportJob']);
        $instance->expects($this->once())
                 ->method('createDownloadProcess')
                 ->with($this->identicalTo($latestVersion))
                 ->willReturn($downloadProcess);
        $instance->expects($this->once())
                 ->method('createExportJob');

        $result = $this->invokeMethod($instance, 'execute', $input, $output);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * @throws ReflectionException
     */
    public function testExecuteWithoutNewVersion(): void
    {
        $version = '1.2.3';
        $latestVersion = '1.2.3';
        $expectedResult = 0;

        $baseInfo = new InfoJson();
        $baseInfo->version = new Version($version);
        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);

        $this->modFileService->expects($this->once())
                             ->method('getInfo')
                             ->with($this->identicalTo(Constant::MOD_NAME_BASE))
                             ->willReturn($baseInfo);

        $this->factorioDownloadService->expects($this->once())
                                      ->method('getLatestVersion')
                                      ->willReturn($latestVersion);

        $instance = $this->createInstance(['createDownloadProcess', 'createExportJob']);
        $instance->expects($this->never())
                 ->method('createDownloadProcess');
        $instance->expects($this->never())
                 ->method('createExportJob');

        $result = $this->invokeMethod($instance, 'execute', $input, $output);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * @throws ReflectionException
     */
    public function testExecuteWithFailedDownload(): void
    {
        $version = '1.2.3';
        $latestVersion = '2.3.4';
        $exitCodeDownload = 1;
        $expectedResult = 1;

        $baseInfo = new InfoJson();
        $baseInfo->version = new Version($version);
        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);

        $downloadProcess = $this->createMock(CommandProcess::class);
        $downloadProcess->expects($this->once())
                        ->method('run')
                        ->with($this->callback(function ($callback): bool {
                            $this->assertIsCallable($callback);
                            $callback('foo', 'bar');
                            return true;
                        }));
        $downloadProcess->expects($this->once())
                        ->method('getExitCode')
                        ->willReturn($exitCodeDownload);

        $this->modFileService->expects($this->once())
                             ->method('getInfo')
                             ->with($this->identicalTo(Constant::MOD_NAME_BASE))
                             ->willReturn($baseInfo);

        $this->factorioDownloadService->expects($this->once())
                                      ->method('getLatestVersion')
                                      ->willReturn($latestVersion);

        $instance = $this->createInstance(['createDownloadProcess', 'createExportJob']);
        $instance->expects($this->once())
                 ->method('createDownloadProcess')
                 ->with($this->identicalTo($latestVersion))
                 ->willReturn($downloadProcess);
        $instance->expects($this->never())
                 ->method('createExportJob');

        $result = $this->invokeMethod($instance, 'execute', $input, $output);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * @throws ReflectionException
     */
    public function testCreateDownloadProcess(): void
    {
        $version = '1.2.3';
        $expectedResult = new CommandProcess(CommandName::DOWNLOAD_FACTORIO, ['1.2.3']);

        $instance = $this->createInstance();
        $result = $this->invokeMethod($instance, 'createDownloadProcess', $version);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @throws ReflectionException
     */
    public function testCreateExportJob(): void
    {
        $expectedRequest = new CreateRequest();
        $expectedRequest->combinationId = Defaults::COMBINATION_ID;
        $expectedRequest->priority = JobPriority::ADMIN;

        $this->combinationApiClient->expects($this->once())
                                   ->method('sendRequest')
                                   ->with($this->equalTo($expectedRequest));

        $instance = $this->createInstance();
        $this->invokeMethod($instance, 'createExportJob');
    }

    /**
     * @throws ReflectionException
     */
    public function testCreateExportJobWithException(): void
    {
        $expectedRequest = new CreateRequest();
        $expectedRequest->combinationId = Defaults::COMBINATION_ID;
        $expectedRequest->priority = JobPriority::ADMIN;

        $this->combinationApiClient->expects($this->once())
                                   ->method('sendRequest')
                                   ->with($this->equalTo($expectedRequest))
                                   ->willThrowException($this->createMock(ClientException::class));

        $this->expectException(CommandException::class);

        $instance = $this->createInstance();
        $this->invokeMethod($instance, 'createExportJob');
    }
}
