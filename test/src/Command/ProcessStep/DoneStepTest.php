<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Command\ProcessStep;

use FactorioItemBrowser\Export\Command\ProcessStep\DoneStep;
use FactorioItemBrowser\Export\Entity\ProcessStepData;
use FactorioItemBrowser\ExportQueue\Client\Constant\JobStatus;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the DoneStep class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Command\ProcessStep\DoneStep
 */
class DoneStepTest extends TestCase
{
    /**
     * Tests the getLabel method.
     * @covers ::getLabel
     */
    public function testGetLabel(): void
    {
        $expectedResult = 'Done.';
        $step = new DoneStep();

        $result = $step->getLabel();
        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the getExportJobStatus method.
     * @covers ::getExportJobStatus
     */
    public function testGetExportJobStatus(): void
    {
        $expectedResult = JobStatus::UPLOADED;
        $step = new DoneStep();

        $result = $step->getExportJobStatus();
        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the run method.
     * @covers ::run
     */
    public function testRun(): void
    {
        /* @var ProcessStepData&MockObject $processStepData */
        $processStepData = $this->createMock(ProcessStepData::class);

        $step = new DoneStep();
        $step->run($processStepData);

        $this->addToAssertionCount(1);
    }
}
