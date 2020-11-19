<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command\ProcessStep;

use FactorioItemBrowser\Export\Console\Console;
use FactorioItemBrowser\Export\Entity\ProcessStepData;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Exception\UploadFailedException;
use FactorioItemBrowser\ExportData\ExportDataService;
use FactorioItemBrowser\ExportQueue\Client\Constant\JobStatus;
use FtpClient\FtpClient;
use FtpClient\FtpException;

/**
 * The step for uploading the export file to the importer.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class UploadStep implements ProcessStepInterface
{
    /**
     * The console.
     * @var Console
     */
    protected $console;

    protected ExportDataService $exportDataService;

    /**
     * The host of the FTP server to upload to.
     * @var string
     */
    protected $ftpHost;

    /**
     * The username to use for logging into the FTP server.
     * @var string
     */
    protected $ftpUsername;

    /**
     * The password to use for logging into the FTP server.
     * @var string
     */
    protected $ftpPassword;

    /**
     * Initializes the step.
     * @param Console $console
     * @param string $uploadFtpHost
     * @param string $uploadFtpUsername
     * @param string $uploadFtpPassword
     */
    public function __construct(
        Console $console,
        ExportDataService $exportDataService,
        string $uploadFtpHost,
        string $uploadFtpUsername,
        string $uploadFtpPassword
    ) {
        $this->console = $console;
        $this->exportDataService = $exportDataService;
        $this->ftpHost = $uploadFtpHost;
        $this->ftpUsername = $uploadFtpUsername;
        $this->ftpPassword = $uploadFtpPassword;
    }

    /**
     * Returns the label to identify the step.
     * @return string
     */
    public function getLabel(): string
    {
        return 'Uploading export file to importer';
    }

    /**
     * Returns the status to set on the export job before running this step.
     * @return string
     */
    public function getExportJobStatus(): string
    {
        return JobStatus::UPLOADING;
    }

    /**
     * Runs the process step.
     * @param ProcessStepData $processStepData
     * @throws ExportException
     */
    public function run(ProcessStepData $processStepData): void
    {
        $fileName = $this->exportDataService->persistExport($processStepData->getExportData());
        $this->console->writeAction(sprintf('Uploading file %s', basename($fileName)));

        try {
            $ftp = $this->createFtpClient();
            $ftp->connect($this->ftpHost);
            $ftp->login($this->ftpUsername, $this->ftpPassword);
            $ftp->pasv(true);

            $ftp->putFromPath($fileName);
        } catch (FtpException $e) {
            throw new UploadFailedException($e->getMessage(), $e);
        }
    }

    /**
     * Creates the FTP client instance.
     * @return FtpClient
     */
    protected function createFtpClient(): FtpClient
    {
        return new FtpClient();
    }
}
