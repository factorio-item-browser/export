<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command\ProcessStep;

use BluePsyduck\LaminasAutoWireFactory\Attribute\ReadConfig;
use FactorioItemBrowser\CombinationApi\Client\Constant\JobStatus;
use FactorioItemBrowser\Export\Constant\ConfigKey;
use FactorioItemBrowser\Export\Output\Console;
use FactorioItemBrowser\Export\Entity\ProcessStepData;
use FactorioItemBrowser\Export\Exception\UploadFailedException;
use FactorioItemBrowser\ExportData\ExportDataService;
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
    private FtpClient $ftpClient;

    public function __construct(
        private readonly Console $console,
        private readonly ExportDataService $exportDataService,
        private readonly LoggerInterface $logger,
        #[ReadConfig(ConfigKey::MAIN, ConfigKey::UPLOAD_FTP, ConfigKey::UPLOAD_FTP_HOST)]
        private readonly string $ftpHost,
        #[ReadConfig(ConfigKey::MAIN, ConfigKey::UPLOAD_FTP, ConfigKey::UPLOAD_FTP_USERNAME)]
        private readonly string $ftpUsername,
        #[ReadConfig(ConfigKey::MAIN, ConfigKey::UPLOAD_FTP, ConfigKey::UPLOAD_FTP_PASSWORD)]
        private readonly string $ftpPassword
    ) {
        $this->ftpClient = new FtpClient();
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
            $this->ftpClient->connect($this->ftpHost);
            $this->ftpClient->login($this->ftpUsername, $this->ftpPassword);
            $this->ftpClient->pasv(true);

            $this->ftpClient->putFromPath($fileName);

            $this->logger->info('Export file uploaded', [
                'combination' => $processStepData->exportData->getCombinationId(),
                'file' => $fileName,
            ]);
        } catch (FtpException $e) {
            throw new UploadFailedException($e->getMessage(), $e);
        }
    }
}
