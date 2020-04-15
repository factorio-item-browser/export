<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Entity;

use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\ExportData\ExportData;
use FactorioItemBrowser\ExportQueue\Client\Entity\Job;

/**
 * The data used for the steps of processing.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ProcessStepData
{
    /**
     * The export job.
     * @var Job
     */
    protected $exportJob;

    /**
     * The export data.
     * @var ExportData
     */
    protected $exportData;

    /**
     * The dump from the export.
     * @var Dump
     */
    protected $dump;

    /**
     * Sets the export job.
     * @param Job $exportJob
     * @return $this
     */
    public function setExportJob(Job $exportJob): self
    {
        $this->exportJob = $exportJob;
        return $this;
    }

    /**
     * Returns the export job.
     * @return Job
     */
    public function getExportJob(): Job
    {
        return $this->exportJob;
    }

    /**
     * Sets the export data.
     * @param ExportData $exportData
     * @return $this
     */
    public function setExportData(ExportData $exportData): self
    {
        $this->exportData = $exportData;
        return $this;
    }

    /**
     * Returns the export data.
     * @return ExportData
     */
    public function getExportData(): ExportData
    {
        return $this->exportData;
    }

    /**
     * Sets the dump from the export.
     * @param Dump $dump
     * @return $this
     */
    public function setDump(Dump $dump): self
    {
        $this->dump = $dump;
        return $this;
    }

    /**
     * Returns the dump from the export.
     * @return Dump
     */
    public function getDump(): Dump
    {
        return $this->dump;
    }
}
