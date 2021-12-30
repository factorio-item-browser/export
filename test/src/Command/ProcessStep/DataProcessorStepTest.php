<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Command\ProcessStep;

use FactorioItemBrowser\CombinationApi\Client\Constant\JobStatus;
use FactorioItemBrowser\Export\Command\ProcessStep\DataProcessorStep;
use FactorioItemBrowser\Export\DataProcessor\DataProcessorInterface;
use FactorioItemBrowser\Export\Entity\ProcessStepData;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\ExportData\ExportData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the DataProcessorStep class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Export\Command\ProcessStep\DataProcessorStep
 */
class DataProcessorStepTest extends TestCase
{
    /** @var DataProcessorInterface&MockObject */
    private DataProcessorInterface $dataProcessor1;
    /** @var DataProcessorInterface&MockObject */
    private DataProcessorInterface $dataProcessor2;

    protected function setUp(): void
    {
        $this->dataProcessor1 = $this->createMock(DataProcessorInterface::class);
        $this->dataProcessor2 = $this->createMock(DataProcessorInterface::class);
    }

    private function createInstance(): DataProcessorStep
    {
        return new DataProcessorStep([$this->dataProcessor1, $this->dataProcessor2]);
    }

    public function testMeta(): void
    {
        $instance = $this->createInstance();

        $this->assertNotEquals('', $instance->getLabel());
        $this->assertSame(JobStatus::PROCESSING, $instance->getExportJobStatus());
    }

    /**
     * @throws ExportException
     */
    public function testRun(): void
    {
        $exportData = $this->createMock(ExportData::class);

        $processStepData = new ProcessStepData();
        $processStepData->exportData = $exportData;

        $this->dataProcessor1->expects($this->once())
                             ->method('process')
                             ->with($this->identicalTo($exportData));
        $this->dataProcessor2->expects($this->once())
                             ->method('process')
                             ->with($this->identicalTo($exportData));

        $instance = $this->createInstance();
        $instance->run($processStepData);
    }
}
