<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Command\ProcessStep;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\CombinationApi\Client\Constant\JobStatus;
use FactorioItemBrowser\Export\Command\ProcessStep\UploadStep;
use FactorioItemBrowser\Export\Output\Console;
use FactorioItemBrowser\Export\Entity\ProcessStepData;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Exception\UploadFailedException;
use FactorioItemBrowser\ExportData\ExportData;
use FactorioItemBrowser\ExportData\ExportDataService;
use FtpClient\FtpClient;
use FtpClient\FtpException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * The PHPUnit test of the UploadStep class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Command\ProcessStep\UploadStep
 */
class UploadStepTest extends TestCase
{
    use ReflectionTrait;

    /** @var Console&MockObject */
    private Console $console;
    /** @var ExportDataService&MockObject */
    private ExportDataService $exportDataService;
    /** @var FtpClient&MockObject */
    private FtpClient $ftpClient;
    /** @var LoggerInterface&MockObject */
    private LoggerInterface $logger;

    protected function setUp(): void
    {
        $this->console = $this->createMock(Console::class);
        $this->exportDataService = $this->createMock(ExportDataService::class);
        $this->ftpClient = $this->createMock(FtpClient::class);
        $this->logger = $this->createMock(LoggerInterface::class);
    }

    private function createInstance(string $host, string $username, string $password): UploadStep
    {
        $instance = new UploadStep(
            $this->console,
            $this->exportDataService,
            $this->logger,
            $host,
            $username,
            $password,
        );

        $this->assertInstanceOf(FtpClient::class, $this->extractProperty($instance, 'ftpClient'));
        $this->injectProperty($instance, 'ftpClient', $this->ftpClient);

        return $instance;
    }

    public function testMeta(): void
    {
        $instance = $this->createInstance('abc', 'def', 'ghi');

        $this->assertNotEquals('', $instance->getLabel());
        $this->assertSame(JobStatus::UPLOADING, $instance->getExportJobStatus());
    }


    /**
     * @throws ExportException
     */
    public function testRun(): void
    {
        $ftpHost = 'abc';
        $ftpUsername = 'def';
        $ftpPassword = 'ghi';
        $fileName = 'jkl/mno.zip';

        $exportData = $this->createMock(ExportData::class);
        $processStepData = new ProcessStepData();
        $processStepData->exportData = $exportData;

        $this->exportDataService->expects($this->once())
                                ->method('persistExport')
                                ->with($this->identicalTo($exportData))
                                ->willReturn($fileName);

        $this->ftpClient->expects($this->once())
                        ->method('connect')
                        ->with($this->identicalTo($ftpHost));
        $this->ftpClient->expects($this->once())
                        ->method('login')
                        ->with($this->identicalTo($ftpUsername), $this->identicalTo($ftpPassword));
        $this->ftpClient->expects($this->once())
                        ->method('__call')
                        ->with($this->identicalTo('pasv'), $this->identicalTo([true]));
        $this->ftpClient->expects($this->once())
                        ->method('putFromPath')
                        ->with($this->identicalTo($fileName));

        $instance = $this->createInstance($ftpHost, $ftpUsername, $ftpPassword);
        $instance->run($processStepData);
    }

    /**
     * @throws ExportException
     */
    public function testRunWithException(): void
    {
        $ftpHost = 'abc';
        $ftpUsername = 'def';
        $ftpPassword = 'ghi';
        $fileName = 'jkl/mno.zip';

        $exportData = $this->createMock(ExportData::class);
        $processStepData = new ProcessStepData();
        $processStepData->exportData = $exportData;

        $this->exportDataService->expects($this->once())
                                ->method('persistExport')
                                ->with($this->identicalTo($exportData))
                                ->willReturn($fileName);

        $this->ftpClient->expects($this->once())
                        ->method('connect')
                        ->with($this->identicalTo($ftpHost));
        $this->ftpClient->expects($this->once())
                        ->method('login')
                        ->with($this->identicalTo($ftpUsername), $this->identicalTo($ftpPassword));
        $this->ftpClient->expects($this->once())
                        ->method('__call')
                        ->with($this->identicalTo('pasv'), $this->identicalTo([true]));
        $this->ftpClient->expects($this->once())
                        ->method('putFromPath')
                        ->with($this->identicalTo($fileName))
                        ->willThrowException($this->createMock(FtpException::class));

        $this->expectException(UploadFailedException::class);

        $instance = $this->createInstance($ftpHost, $ftpUsername, $ftpPassword);
        $instance->run($processStepData);
    }
}
