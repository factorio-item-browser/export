<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Command\ProcessStep;

use FactorioItemBrowser\Export\Command\ProcessStep\DoneStep;
use FactorioItemBrowser\Export\Entity\ProcessStepData;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\ExportQueue\Client\Constant\JobStatus;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the DoneStep class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Export\Command\ProcessStep\DoneStep
 */
class DoneStepTest extends TestCase
{
    /**
     * @throws ExportException
     */
    public function test(): void
    {
        $instance = new DoneStep();

        $this->assertNotEquals('', $instance->getLabel());
        $this->assertSame(JobStatus::UPLOADED, $instance->getExportJobStatus());
        $instance->run($this->createMock(ProcessStepData::class));
    }
}
