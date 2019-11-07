<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Entity;

use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\Entity\ProcessStepData;
use FactorioItemBrowser\ExportData\ExportData;
use FactorioItemBrowser\ExportQueue\Client\Entity\Job;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the ProcessStepData class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Entity\ProcessStepData
 */
class ProcessStepDataTest extends TestCase
{
    /**
     * Tests the setting and getting the export job.
     * @covers ::getExportJob
     * @covers ::setExportJob
     */
    public function testSetAndGetExportJob(): void
    {
        /* @var Job&MockObject $exportJob */
        $exportJob = $this->createMock(Job::class);
        $entity = new ProcessStepData();

        $this->assertSame($entity, $entity->setExportJob($exportJob));
        $this->assertSame($exportJob, $entity->getExportJob());
    }

    /**
     * Tests the setting and getting the export data.
     * @covers ::getExportData
     * @covers ::setExportData
     */
    public function testSetAndGetExportData(): void
    {
        /* @var ExportData&MockObject $exportData */
        $exportData = $this->createMock(ExportData::class);
        $entity = new ProcessStepData();

        $this->assertSame($entity, $entity->setExportData($exportData));
        $this->assertSame($exportData, $entity->getExportData());
    }

    /**
     * Tests the setting and getting the dump.
     * @covers ::getDump
     * @covers ::setDump
     */
    public function testSetAndGetDump(): void
    {
        /* @var Dump&MockObject $dump */
        $dump = $this->createMock(Dump::class);
        $entity = new ProcessStepData();

        $this->assertSame($entity, $entity->setDump($dump));
        $this->assertSame($dump, $entity->getDump());
    }
}
