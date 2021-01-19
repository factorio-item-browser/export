<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Command\ProcessStep;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Export\Command\ProcessStep\FactorioStep;
use FactorioItemBrowser\Export\Entity\ProcessStepData;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Output\Console;
use FactorioItemBrowser\Export\Service\FactorioExecutionService;
use FactorioItemBrowser\ExportQueue\Client\Constant\JobStatus;
use FactorioItemBrowser\ExportQueue\Client\Entity\Job;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the FactorioStep class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Export\Command\ProcessStep\FactorioStep
 */
class FactorioStepTest extends TestCase
{
    use ReflectionTrait;

    /** @var Console&MockObject */
    private Console $console;
    /** @var FactorioExecutionService&MockObject */
    private FactorioExecutionService $factorioExecutionService;

    protected function setUp(): void
    {
        $this->console = $this->createMock(Console::class);
        $this->factorioExecutionService = $this->createMock(FactorioExecutionService::class);
    }

    public function createInstance(): FactorioStep
    {
        return new FactorioStep($this->console, $this->factorioExecutionService);
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
        $combinationId = 'abc';
        $modNames = ['def', 'ghi'];

        $exportJob = new Job();
        $exportJob->setCombinationId($combinationId)
                  ->setModNames($modNames);

        $processStepData = new ProcessStepData();
        $processStepData->exportJob = $exportJob;

        $this->factorioExecutionService->expects($this->once())
                                       ->method('prepare')
                                       ->with($this->identicalTo($combinationId), $this->identicalTo($modNames));
        $this->factorioExecutionService->expects($this->once())
                                       ->method('execute')
                                       ->with($this->identicalTo($combinationId));

        $instance = $this->createInstance();
        $instance->run($processStepData);
    }
}
