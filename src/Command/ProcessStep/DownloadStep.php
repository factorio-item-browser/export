<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command\ProcessStep;

use FactorioItemBrowser\Export\Entity\ProcessStepData;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Mod\ModDownloader;
use FactorioItemBrowser\ExportQueue\Client\Constant\JobStatus;

/**
 * The step for downloading all the required mods.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class DownloadStep implements ProcessStepInterface
{
    /**
     * The mod downloader.
     * @var ModDownloader
     */
    protected $modDownloader;

    /**
     * Initializes the step.
     * @param ModDownloader $modDownloader
     */
    public function __construct(ModDownloader $modDownloader)
    {
        $this->modDownloader = $modDownloader;
    }

    /**
     * Returns the label to identify the step.
     * @return string
     */
    public function getLabel(): string
    {
        return 'Downloading mods from the Mod Portal';
    }

    /**
     * Returns the status to set on the export job before running this step.
     * @return string
     */
    public function getExportJobStatus(): string
    {
        return JobStatus::DOWNLOADING;
    }

    /**
     * Runs the process step.
     * @param ProcessStepData $processStepData
     * @throws ExportException
     */
    public function run(ProcessStepData $processStepData): void
    {
        $this->modDownloader->download($processStepData->getExportJob()->getModNames());
    }
}
