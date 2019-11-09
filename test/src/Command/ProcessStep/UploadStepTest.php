<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Command\ProcessStep;

use FactorioItemBrowser\Export\Command\ProcessStep\UploadStep;
use FactorioItemBrowser\Export\Entity\ProcessStepData;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\ExportData\ExportData;
use FactorioItemBrowser\ExportQueue\Client\Constant\JobStatus;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the UploadStep class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Command\ProcessStep\UploadStep
 */
class UploadStepTest extends TestCase
{
    /**
     * Tests the getLabel method.
     * @covers ::getLabel
     */
    public function testGetLabel(): void
    {
        $expectedResult = 'Uploading export file to importer';
        $step = new UploadStep();

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
        $step = new UploadStep();

        $result = $step->getExportJobStatus();
        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the run method.
     * @throws ExportException
     * @covers ::run
     */
    public function testRun(): void
    {
        /* @var ExportData&MockObject $exportData */
        $exportData = $this->createMock(ExportData::class);
        $exportData->expects($this->once())
                   ->method('persist');

        $data = new ProcessStepData();
        $data->setExportData($exportData);

        $step = new UploadStep();
        $step->run($data);
    }
}
