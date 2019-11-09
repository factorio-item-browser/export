<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Command\ProcessStep;

use FactorioItemBrowser\Export\Command\ProcessStep\UploadStep;
use FactorioItemBrowser\ExportQueue\Client\Constant\JobStatus;
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
}
