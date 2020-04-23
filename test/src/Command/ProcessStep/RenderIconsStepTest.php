<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Command\ProcessStep;

use BluePsyduck\SymfonyProcessManager\ProcessManager;
use BluePsyduck\SymfonyProcessManager\ProcessManagerInterface;
use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Export\Command\ProcessStep\RenderIconsStep;
use FactorioItemBrowser\Export\Console\Console;
use FactorioItemBrowser\Export\Entity\ProcessStepData;
use FactorioItemBrowser\Export\Process\RenderIconProcess;
use FactorioItemBrowser\Export\Process\RenderIconProcessFactory;
use FactorioItemBrowser\ExportData\Entity\Combination;
use FactorioItemBrowser\ExportData\Entity\Icon;
use FactorioItemBrowser\ExportData\ExportData;
use FactorioItemBrowser\ExportQueue\Client\Constant\JobStatus;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the RenderIconsStep class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Command\ProcessStep\RenderIconsStep
 */
class RenderIconsStepTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked console.
     * @var Console&MockObject
     */
    protected $console;

    /**
     * The mocked render icon process factory.
     * @var RenderIconProcessFactory&MockObject
     */
    protected $renderIconProcessFactory;

    /**
     * Sets up the test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->console = $this->createMock(Console::class);
        $this->renderIconProcessFactory = $this->createMock(RenderIconProcessFactory::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $parallelProcesses = 42;

        $step = new RenderIconsStep($this->console, $this->renderIconProcessFactory, $parallelProcesses);

        $this->assertSame($this->console, $this->extractProperty($step, 'console'));
        $this->assertSame($this->renderIconProcessFactory, $this->extractProperty($step, 'renderIconProcessFactory'));
        $this->assertSame($parallelProcesses, $this->extractProperty($step, 'numberOfParallelRenderProcesses'));
    }

    /**
     * Tests the getLabel method.
     * @covers ::getLabel
     */
    public function testGetLabel(): void
    {
        $expectedResult = 'Rendering the thumbnails and icons';
        $step = new RenderIconsStep($this->console, $this->renderIconProcessFactory, 42);

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
        $step = new RenderIconsStep($this->console, $this->renderIconProcessFactory, 42);

        $result = $step->getExportJobStatus();
        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the run method.
     * @covers ::run
     */
    public function testRun(): void
    {
        /* @var Icon&MockObject $icon1 */
        $icon1 = $this->createMock(Icon::class);
        /* @var Icon&MockObject $icon2 */
        $icon2 = $this->createMock(Icon::class);

        /* @var RenderIconProcess&MockObject $process1 */
        $process1 = $this->createMock(RenderIconProcess::class);
        /* @var RenderIconProcess&MockObject $process2 */
        $process2 = $this->createMock(RenderIconProcess::class);

        $combination = new Combination();
        $combination->setIcons([$icon1, $icon2]);

        /* @var ExportData&MockObject $exportData */
        $exportData = $this->createMock(ExportData::class);
        $exportData->expects($this->once())
                   ->method('getCombination')
                   ->willReturn($combination);

        $data = new ProcessStepData();
        $data->setExportData($exportData);

        /* @var ProcessManagerInterface&MockObject $processManager */
        $processManager = $this->createMock(ProcessManagerInterface::class);
        $processManager->expects($this->exactly(2))
                       ->method('addProcess')
                       ->withConsecutive(
                           [$this->identicalTo($process1)],
                           [$this->identicalTo($process2)]
                       );
        $processManager->expects($this->once())
                       ->method('waitForAllProcesses');

        $this->renderIconProcessFactory->expects($this->exactly(2))
                                       ->method('create')
                                       ->withConsecutive(
                                           [$this->identicalTo($icon1)],
                                           [$this->identicalTo($icon2)]
                                       )
                                       ->willReturnOnConsecutiveCalls(
                                           $process1,
                                           $process2
                                       );

        /* @var RenderIconsStep&MockObject $step */
        $step = $this->getMockBuilder(RenderIconsStep::class)
                     ->onlyMethods(['createProcessManager'])
                     ->setConstructorArgs([$this->console, $this->renderIconProcessFactory, 42])
                     ->getMock();
        $step->expects($this->once())
             ->method('createProcessManager')
             ->willReturn($processManager);

        $step->run($data);
    }

    /**
     * Tests the createProcessManager method.
     * @throws ReflectionException
     * @covers ::createProcessManager
     */
    public function testCreateProcessManager(): void
    {
        $parallelProcesses = 42;

        /* @var ExportData&MockObject $exportData */
        $exportData = $this->createMock(ExportData::class);
        /* @var RenderIconProcess&MockObject $process */
        $process = $this->createMock(RenderIconProcess::class);

        /* @var RenderIconsStep&MockObject $step */
        $step = $this->getMockBuilder(RenderIconsStep::class)
                     ->onlyMethods(['handleProcessStart', 'handleProcessFinish'])
                     ->setConstructorArgs([$this->console, $this->renderIconProcessFactory, $parallelProcesses])
                     ->getMock();
        $step->expects($this->once())
             ->method('handleProcessStart')
             ->with($this->identicalTo($process));
        $step->expects($this->once())
             ->method('handleProcessFinish')
             ->with($this->identicalTo($exportData), $this->identicalTo($process));

        /* @var ProcessManager $result */
        $result = $this->invokeMethod($step, 'createProcessManager', $exportData);
        $this->assertSame($parallelProcesses, $this->extractProperty($result, 'numberOfParallelProcesses'));

        $startCallback = $this->extractProperty($result, 'processStartCallback');
        $this->assertIsCallable($startCallback);
        $startCallback($process);

        $finishCallback = $this->extractProperty($result, 'processFinishCallback');
        $this->assertIsCallable($finishCallback);
        $finishCallback($process);
    }

    /**
     * Tests the handleProcessStart method.
     * @throws ReflectionException
     * @covers ::handleProcessStart
     */
    public function testHandleProcessStart(): void
    {
        $icon = new Icon();
        $icon->setId('abc');

        /* @var RenderIconProcess&MockObject $process */
        $process = $this->createMock(RenderIconProcess::class);
        $process->expects($this->once())
                ->method('getIcon')
                ->willReturn($icon);

        $this->console->expects($this->once())
                      ->method('writeAction')
                      ->with($this->identicalTo('Rendering icon abc'));

        $step = new RenderIconsStep($this->console, $this->renderIconProcessFactory, 42);
        $this->invokeMethod($step, 'handleProcessStart', $process);
    }

    /**
     * Tests the handleProcessFinish method.
     * @throws ReflectionException
     * @covers ::handleProcessFinish
     */
    public function testHandleProcessFinish(): void
    {
        $output = 'abc';

        /* @var Icon&MockObject $icon */
        $icon = $this->createMock(Icon::class);

        /* @var RenderIconProcess&MockObject $process */
        $process = $this->createMock(RenderIconProcess::class);
        $process->expects($this->once())
                ->method('isSuccessful')
                ->willReturn(true);
        $process->expects($this->once())
                ->method('getIcon')
                ->willReturn($icon);
        $process->expects($this->once())
                ->method('getOutput')
                ->willReturn($output);

        /* @var ExportData&MockObject $exportData */
        $exportData = $this->createMock(ExportData::class);
        $exportData->expects($this->once())
                   ->method('addRenderedIcon')
                   ->with($this->identicalTo($icon), $this->identicalTo($output));

        $step = new RenderIconsStep($this->console, $this->renderIconProcessFactory, 42);
        $this->invokeMethod($step, 'handleProcessFinish', $exportData, $process);
    }

    /**
     * Tests the handleProcessFinish method.
     * @throws ReflectionException
     * @covers ::handleProcessFinish
     */
    public function testHandleProcessFinishWithError(): void
    {
        $output = 'abc';

        /* @var RenderIconProcess&MockObject $process */
        $process = $this->createMock(RenderIconProcess::class);
        $process->expects($this->once())
                ->method('isSuccessful')
                ->willReturn(false);
        $process->expects($this->once())
                ->method('getOutput')
                ->willReturn($output);

        /* @var ExportData&MockObject $exportData */
        $exportData = $this->createMock(ExportData::class);
        $exportData->expects($this->never())
                   ->method('addRenderedIcon');

        $this->console->expects($this->once())
                      ->method('writeData')
                      ->with($this->identicalTo($output));

        $step = new RenderIconsStep($this->console, $this->renderIconProcessFactory, 42);
        $this->invokeMethod($step, 'handleProcessFinish', $exportData, $process);
    }
}
