<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Command\ProcessStep;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Export\Command\ProcessStep\FactorioStep;
use FactorioItemBrowser\Export\Entity\ProcessStepData;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Factorio\Instance;
use FactorioItemBrowser\ExportQueue\Client\Constant\JobStatus;
use FactorioItemBrowser\ExportQueue\Client\Entity\Job;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the FactorioStep class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Command\ProcessStep\FactorioStep
 */
class FactorioStepTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked instance.
     * @var Instance&MockObject
     */
    protected $instance;

    /**
     * Sets up the test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = $this->createMock(Instance::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $step = new FactorioStep($this->instance);

        $this->assertSame($this->instance, $this->extractProperty($step, 'instance'));
    }

    /**
     * Tests the getLabel method.
     * @covers ::getLabel
     */
    public function testGetLabel(): void
    {
        $expectedResult = 'Running Factorio';
        $step = new FactorioStep($this->instance);

        $result = $step->getLabel();
        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the getExportJobStatus method.
     * @covers ::getExportJobStatus
     */
    public function testGetExportJobStatus(): void
    {
        $expectedResult = JobStatus::PROCESSING;
        $step = new FactorioStep($this->instance);

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
        $combinationId = 'abc';
        $modNames = ['def', 'ghi'];

        $exportJob = new Job();
        $exportJob->setCombinationId($combinationId)
                  ->setModNames($modNames);

        $data = new ProcessStepData();
        $data->setExportJob($exportJob);

        $this->instance->expects($this->once())
                       ->method('run')
                       ->with($this->identicalTo($combinationId), $this->identicalTo($modNames));

        $step = new FactorioStep($this->instance);
        $step->run($data);
    }
}
