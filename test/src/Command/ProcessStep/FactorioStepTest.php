<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Command\ProcessStep;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\CombinationApi\Client\Constant\JobStatus;
use FactorioItemBrowser\CombinationApi\Client\Transfer\Combination;
use FactorioItemBrowser\Export\Command\ProcessStep\FactorioStep;
use FactorioItemBrowser\Export\Entity\ProcessStepData;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Output\Console;
use FactorioItemBrowser\Export\Service\FactorioExecutionService;
use FactorioItemBrowser\ExportData\ExportData;
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

    private function createInstance(): FactorioStep
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
        $exportData = $this->createMock(ExportData::class);

        $combination = new Combination();
        $combination->id = $combinationId;
        $combination->modNames = $modNames;

        $processStepData = new ProcessStepData();
        $processStepData->combination = $combination;
        $processStepData->exportData = $exportData;

        $this->factorioExecutionService->expects($this->once())
                                       ->method('prepare')
                                       ->with($this->identicalTo($combinationId), $this->identicalTo($modNames));
        $this->factorioExecutionService->expects($this->once())
                                       ->method('execute')
                                       ->with($this->identicalTo($exportData), $this->identicalTo($combinationId));

        $instance = $this->createInstance();
        $instance->run($processStepData);
    }
}
