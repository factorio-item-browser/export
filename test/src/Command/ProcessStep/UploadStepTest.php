<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Command\ProcessStep;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Export\Command\ProcessStep\UploadStep;
use FactorioItemBrowser\Export\Console\Console;
use FactorioItemBrowser\Export\Entity\ProcessStepData;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Exception\UploadFailedException;
use FactorioItemBrowser\ExportData\ExportData;
use FactorioItemBrowser\ExportData\ExportDataService;
use FactorioItemBrowser\ExportQueue\Client\Constant\JobStatus;
use FtpClient\FtpClient;
use FtpClient\FtpException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

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
    private $console;
    /** @var ExportDataService&MockObject */
    private ExportDataService $exportDataService;

    protected function setUp(): void
    {
        $this->console = $this->createMock(Console::class);
        $this->exportDataService = $this->createMock(ExportDataService::class);
    }

    /**
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $ftpHost = 'abc';
        $ftpUsername = 'def';
        $ftpPassword = 'ghi';

        $step = new UploadStep($this->console, $this->exportDataService, $ftpHost, $ftpUsername, $ftpPassword);

        $this->assertSame($this->console, $this->extractProperty($step, 'console'));
        $this->assertSame($ftpHost, $this->extractProperty($step, 'ftpHost'));
        $this->assertSame($ftpUsername, $this->extractProperty($step, 'ftpUsername'));
        $this->assertSame($ftpPassword, $this->extractProperty($step, 'ftpPassword'));
    }

    /**
     * Tests the getLabel method.
     * @covers ::getLabel
     */
    public function testGetLabel(): void
    {
        $expectedResult = 'Uploading export file to importer';
        $step = new UploadStep($this->console, $this->exportDataService, 'foo', 'bar', 'baz');

        $result = $step->getLabel();
        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the getExportJobStatus method.
     * @covers ::getExportJobStatus
     */
    public function testGetExportJobStatus(): void
    {
        $expectedResult = JobStatus::UPLOADING;
        $step = new UploadStep($this->console, $this->exportDataService, 'foo', 'bar', 'baz');

        $result = $step->getExportJobStatus();
        $this->assertSame($expectedResult, $result);
    }

    /**
     * @throws ExportException
     * @covers ::run
     */
    public function testRun(): void
    {
        $ftpHost = 'abc';
        $ftpUsername = 'def';
        $ftpPassword = 'ghi';
        $fileName = 'jkl/mno.zip';

        $exportData = $this->createMock(ExportData::class);

        $data = new ProcessStepData();
        $data->setExportData($exportData);

        $ftpClient = $this->createMock(FtpClient::class);
        $ftpClient->expects($this->once())
                  ->method('connect')
                  ->with($this->identicalTo($ftpHost));
        $ftpClient->expects($this->once())
                  ->method('login')
                  ->with($this->identicalTo($ftpUsername), $this->identicalTo($ftpPassword));
        $ftpClient->expects($this->once())
                  ->method('__call')
                  ->with($this->identicalTo('pasv'), $this->identicalTo([true]));
        $ftpClient->expects($this->once())
                  ->method('putFromPath')
                  ->with($this->identicalTo($fileName));

        $this->console->expects($this->once())
                      ->method('writeAction')
                      ->with($this->identicalTo('Uploading file mno.zip'));

        $this->exportDataService->expects($this->once())
                                ->method('persistExport')
                                ->with($this->identicalTo($exportData))
                                ->willReturn($fileName);

        $step = $this->getMockBuilder(UploadStep::class)
                     ->onlyMethods(['createFtpClient'])
                     ->setConstructorArgs([
                         $this->console,
                         $this->exportDataService,
                         $ftpHost,
                         $ftpUsername,
                         $ftpPassword,
                     ])
                     ->getMock();
        $step->expects($this->once())
             ->method('createFtpClient')
             ->willReturn($ftpClient);

        $step->run($data);
    }

    /**
     * @throws ExportException
     * @covers ::run
     */
    public function testRunWithException(): void
    {
        $ftpHost = 'abc';
        $ftpUsername = 'def';
        $ftpPassword = 'ghi';
        $fileName = 'jkl/mno.zip';

        $exportData = $this->createMock(ExportData::class);

        $data = new ProcessStepData();
        $data->setExportData($exportData);

        $ftpClient = $this->createMock(FtpClient::class);
        $ftpClient->expects($this->once())
                  ->method('connect')
                  ->with($this->identicalTo($ftpHost));
        $ftpClient->expects($this->once())
                  ->method('login')
                  ->with($this->identicalTo($ftpUsername), $this->identicalTo($ftpPassword));
        $ftpClient->expects($this->once())
                  ->method('__call')
                  ->with($this->identicalTo('pasv'), $this->identicalTo([true]));
        $ftpClient->expects($this->once())
                  ->method('putFromPath')
                  ->with($this->identicalTo($fileName))
                  ->willThrowException($this->createMock(FtpException::class));

        $this->console->expects($this->once())
                      ->method('writeAction')
                      ->with($this->identicalTo('Uploading file mno.zip'));

        $this->exportDataService->expects($this->once())
                                ->method('persistExport')
                                ->with($this->identicalTo($exportData))
                                ->willReturn($fileName);

        $this->expectException(UploadFailedException::class);

        $step = $this->getMockBuilder(UploadStep::class)
                     ->onlyMethods(['createFtpClient'])
                     ->setConstructorArgs([
                         $this->console,
                         $this->exportDataService,
                         $ftpHost,
                         $ftpUsername,
                         $ftpPassword,
                     ])
                     ->getMock();
        $step->expects($this->once())
             ->method('createFtpClient')
             ->willReturn($ftpClient);

        $step->run($data);
    }

    /**
     * @throws ReflectionException
     * @covers ::createFtpClient
     */
    public function testCreateFtpClient(): void
    {
        $expectedResult = new FtpClient();

        $step = new UploadStep($this->console, $this->exportDataService, 'foo', 'bar', 'baz');
        $result = $this->invokeMethod($step, 'createFtpClient');

        $this->assertEquals($expectedResult, $result);
    }
}
