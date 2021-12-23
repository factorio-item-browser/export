<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command\ProcessStep;

use Blazon\PSR11FlySystem\FlySystemFactory;
use BluePsyduck\LaminasAutoWireFactory\Attribute\Alias;
use BluePsyduck\LaminasAutoWireFactory\Attribute\ReadConfig;
use FactorioItemBrowser\CombinationApi\Client\Constant\JobStatus;
use FactorioItemBrowser\Export\Constant\ConfigKey;
use FactorioItemBrowser\Export\Constant\ServiceName;
use FactorioItemBrowser\Export\Output\Console;
use FactorioItemBrowser\Export\Entity\ProcessStepData;
use FactorioItemBrowser\Export\Exception\UploadFailedException;
use FactorioItemBrowser\ExportData\ExportDataService;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\Ftp\FtpAdapter;
use League\Flysystem\Ftp\FtpConnectionOptions;
use Psr\Log\LoggerInterface;

/**
 * The step for uploading the export file to the importer.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class UploadStep implements ProcessStepInterface
{
    public function __construct(
        private readonly Console $console,
        private readonly ExportDataService $exportDataService,
        private readonly LoggerInterface $logger,
        #[Alias(ServiceName::FLYSYSTEM_UPLOAD)]
        private readonly FilesystemOperator $uploadFileSystem,
    ) {
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
            $this->uploadFileSystem->write(basename($fileName), (string) file_get_contents($fileName));

            $this->logger->info('Export file uploaded', [
                'combination' => $processStepData->exportData->getCombinationId(),
                'file' => $fileName,
            ]);
        } catch (FilesystemException $e) {
            throw new UploadFailedException($e->getMessage(), $e);
        }
    }
}
