<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command\ProcessStep;

use FactorioItemBrowser\Export\Output\Console;
use FactorioItemBrowser\Export\Entity\ProcessStepData;
use FactorioItemBrowser\Export\Exception\UploadFailedException;
use FactorioItemBrowser\ExportData\ExportDataService;
use FactorioItemBrowser\ExportQueue\Client\Constant\JobStatus;
use FtpClient\FtpClient;
use FtpClient\FtpException;
use Psr\Log\LoggerInterface;

/**
 * The step for uploading the export file to the importer.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class UploadStep implements ProcessStepInterface
{
    protected Console $console;
    protected ExportDataService $exportDataService;
    protected LoggerInterface $logger;
    protected string $ftpHost;
    protected string $ftpUsername;
    protected string $ftpPassword;

    public function __construct(
        Console $console,
        ExportDataService $exportDataService,
        LoggerInterface $logger,
        string $uploadFtpHost,
        string $uploadFtpUsername,
        string $uploadFtpPassword
    ) {
        $this->console = $console;
        $this->exportDataService = $exportDataService;
        $this->logger = $logger;
        $this->ftpHost = $uploadFtpHost;
        $this->ftpUsername = $uploadFtpUsername;
        $this->ftpPassword = $uploadFtpPassword;
    }

    public function getLabel(): string
    {
        return 'Uploading export file to importer';
    }

    public function getExportJobStatus(): string
    {
        return JobStatus::UPLOADING;
    }

    public function run(ProcessStepData $processStepData): void
    {
        $this->console->writeAction('Persisting export data');
        $fileName = $this->exportDataService->persistExport($processStepData->exportData);
        $this->console->writeAction(sprintf('Uploading file %s', basename($fileName)));

        try {
            $ftp = $this->createFtpClient();
            $ftp->connect($this->ftpHost);
            $ftp->login($this->ftpUsername, $this->ftpPassword);
            $ftp->pasv(true);

            $ftp->putFromPath($fileName);

            $this->logger->info('Export file uploaded', [
                'combination' => $processStepData->exportData->getCombinationId(),
                'file' => $fileName,
            ]);
        } catch (FtpException $e) {
            throw new UploadFailedException($e->getMessage(), $e);
        }
    }

    protected function createFtpClient(): FtpClient
    {
        return new FtpClient();
    }
}
