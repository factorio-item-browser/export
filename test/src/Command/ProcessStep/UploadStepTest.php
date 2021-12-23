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
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use org\bovigo\vfs\vfsStream;
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
    /** @var LoggerInterface&MockObject */
    private LoggerInterface $logger;
    /** @var FilesystemOperator&MockObject $uploadFileSystem */
    private FilesystemOperator $uploadFileSystem;

    protected function setUp(): void
    {
        $this->console = $this->createMock(Console::class);
        $this->exportDataService = $this->createMock(ExportDataService::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->uploadFileSystem = $this->createMock(FilesystemOperator::class);
    }

    private function createInstance(): UploadStep
    {
        return new UploadStep(
            $this->console,
            $this->exportDataService,
            $this->logger,
            $this->uploadFileSystem,
        );
    }

    public function testMeta(): void
    {
        $instance = $this->createInstance();

        $this->assertNotEquals('', $instance->getLabel());
        $this->assertSame(JobStatus::UPLOADING, $instance->getExportJobStatus());
    }

    /**
     * @throws ExportException
     */
    public function testRun(): void
    {
        $contents = 'abc';
        $exportData = $this->createMock(ExportData::class);
        $processStepData = new ProcessStepData();
        $processStepData->exportData = $exportData;

        vfsStream::setup('root', null, [
            'def.zip' => $contents,
        ]);
        $fileName = vfsStream::url('root/def.zip');
        $expectedFileName = 'def.zip';

        $this->exportDataService->expects($this->once())
                                ->method('persistExport')
                                ->with($this->identicalTo($exportData))
                                ->willReturn($fileName);

        $this->uploadFileSystem->expects($this->once())
                               ->method('write')
                               ->with($this->identicalTo($expectedFileName), $this->identicalTo($contents));

        $instance = $this->createInstance();
        $instance->run($processStepData);
    }

    /**
     * @throws ExportException
     */
    public function testRunWithException(): void
    {
        $contents = 'abc';
        $exportData = $this->createMock(ExportData::class);
        $processStepData = new ProcessStepData();
        $processStepData->exportData = $exportData;

        vfsStream::setup('root', null, [
            'def.zip' => $contents,
        ]);
        $fileName = vfsStream::url('root/def.zip');
        $expectedFileName = 'def.zip';

        $this->exportDataService->expects($this->once())
                                ->method('persistExport')
                                ->with($this->identicalTo($exportData))
                                ->willReturn($fileName);

        $this->uploadFileSystem->expects($this->once())
                               ->method('write')
                               ->with($this->identicalTo($expectedFileName), $this->identicalTo($contents))
                               ->willThrowException($this->createMock(FilesystemException::class));

        $this->expectException(UploadFailedException::class);

        $instance = $this->createInstance();
        $instance->run($processStepData);
    }
}
