<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\DataProcessor;

use BluePsyduck\SymfonyProcessManager\ProcessManagerInterface;
use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Export\DataProcessor\IconRenderer;
use FactorioItemBrowser\Export\Output\Console;
use FactorioItemBrowser\Export\Output\ProgressBar;
use FactorioItemBrowser\Export\Process\RenderIconProcess;
use FactorioItemBrowser\Export\Process\RenderIconProcessFactory;
use FactorioItemBrowser\ExportData\Collection\DictionaryInterface;
use FactorioItemBrowser\ExportData\Entity\Icon;
use FactorioItemBrowser\ExportData\ExportData;
use FactorioItemBrowser\ExportData\Storage\Storage;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use ReflectionException;
use Symfony\Component\Console\Output\ConsoleSectionOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The PHPUnit test of the IconRenderer class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Export\DataProcessor\IconRenderer
 */
class IconRendererTest extends TestCase
{
    use ReflectionTrait;

    /** @var Console&MockObject */
    private Console $console;
    /** @var LoggerInterface&MockObject */
    private LoggerInterface $logger;
    /** @var RenderIconProcessFactory&MockObject */
    private RenderIconProcessFactory $renderIconProcessFactory;
    /** @var OutputInterface&MockObject */
    private OutputInterface $errorOutput;
    /** @var ProgressBar&MockObject */
    private ProgressBar $progressBar;
    private int $numberOfParallelRenderProcesses = 42;

    protected function setUp(): void
    {
        $this->console = $this->createMock(Console::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->renderIconProcessFactory = $this->createMock(RenderIconProcessFactory::class);
        $this->errorOutput = $this->createMock(ConsoleSectionOutput::class);
        $this->progressBar = $this->createMock(ProgressBar::class);
    }

    /**
     * @param array<string> $mockedMethods
     * @return IconRenderer&MockObject
     * @throws ReflectionException
     */
    private function createInstance(array $mockedMethods = []): IconRenderer
    {
        $this->console->expects($this->any())
                      ->method('createProgressBar')
                      ->willReturn($this->progressBar);
        $this->console->expects($this->any())
                      ->method('createSection')
                      ->willReturn($this->errorOutput);

        $instance = $this->getMockBuilder(IconRenderer::class)
                         ->onlyMethods($mockedMethods)
                         ->setConstructorArgs([
                             $this->console,
                             $this->logger,
                             $this->renderIconProcessFactory,
                             $this->numberOfParallelRenderProcesses,
                         ])
                         ->getMock();

        $this->injectProperty($instance, 'errorOutput', $this->errorOutput);
        $this->injectProperty($instance, 'progressBar', $this->progressBar);

        return $instance;
    }


    /**
     * @throws ReflectionException
     */
    public function testProcess(): void
    {
        $icon1 = $this->createMock(Icon::class);
        $icon2 = $this->createMock(Icon::class);

        $process1 = $this->createMock(RenderIconProcess::class);
        $process2 = $this->createMock(RenderIconProcess::class);

        $exportData = new ExportData($this->createMock(Storage::class), 'foo');
        $exportData->getIcons()->add($icon1)
                               ->add($icon2);

        $processManager = $this->createMock(ProcessManagerInterface::class);
        $processManager->expects($this->exactly(2))
                       ->method('addProcess')
                       ->withConsecutive(
                           [$this->identicalTo($process1)],
                           [$this->identicalTo($process2)]
                       );
        $processManager->expects($this->once())
                       ->method('waitForAllProcesses');

        $this->progressBar->expects($this->once())
                          ->method('setNumberOfSteps')
                          ->with($this->identicalTo(2));

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

        $instance = $this->createInstance(['createProcessManager']);
        $instance->expects($this->once())
                 ->method('createProcessManager')
                 ->willReturn($processManager);

        $instance->process($exportData);
    }

    /**
     * @throws ReflectionException
     */
    public function testCreateProcessManager(): void
    {
        $exportData = $this->createMock(ExportData::class);
        $process = $this->createMock(RenderIconProcess::class);

        $instance = $this->createInstance(['handleProcessStart', 'handleProcessFinish']);
        $instance->expects($this->once())
                 ->method('handleProcessStart')
                 ->with($this->identicalTo($process));
        $instance->expects($this->once())
                 ->method('handleProcessFinish')
                 ->with($this->identicalTo($exportData), $this->identicalTo($process));

        /** @var ProcessManagerInterface $result */
        $result = $this->invokeMethod($instance, 'createProcessManager', $exportData);
        $this->assertSame(
            $this->numberOfParallelRenderProcesses,
            $this->extractProperty($result, 'numberOfParallelProcesses'),
        );

        $startCallback = $this->extractProperty($result, 'processStartCallback');
        $this->assertIsCallable($startCallback);
        $startCallback($process);

        $finishCallback = $this->extractProperty($result, 'processFinishCallback');
        $this->assertIsCallable($finishCallback);
        $finishCallback($process);
    }


    /**
     * @throws ReflectionException
     */
    public function testHandleProcessStart(): void
    {
        $iconId = 'abc';

        $icon = new Icon();
        $icon->id = $iconId;

        $process = $this->createMock(RenderIconProcess::class);
        $process->expects($this->any())
                ->method('getIcon')
                ->willReturn($icon);

        $this->progressBar->expects($this->once())
                          ->method('start')
                          ->with($this->identicalTo($iconId), $this->stringContains($iconId));

        $instance = $this->createInstance();
        $this->invokeMethod($instance, 'handleProcessStart', $process);
    }

    /**
     * @throws ReflectionException
     */
    public function testHandleProcessFinish(): void
    {
        $output = 'abc';
        $iconId = 'def';
        $icon = new Icon();
        $icon->id = $iconId;

        $process = $this->createMock(RenderIconProcess::class);
        $process->expects($this->once())
                ->method('isSuccessful')
                ->willReturn(true);
        $process->expects($this->any())
                ->method('getIcon')
                ->willReturn($icon);
        $process->expects($this->once())
                ->method('getOutput')
                ->willReturn($output);

        $renderedIcons = $this->createMock(DictionaryInterface::class);
        $renderedIcons->expects($this->once())
                      ->method('set')
                      ->with($this->identicalTo($iconId), $this->identicalTo($output));

        $exportData = $this->createMock(ExportData::class);
        $exportData->expects($this->once())
                   ->method('getRenderedIcons')
                   ->willReturn($renderedIcons);

        $this->progressBar->expects($this->once())
                          ->method('finish')
                          ->with($this->identicalTo($iconId));

        $this->errorOutput->expects($this->never())
                          ->method('write');

        $instance = $this->createInstance();
        $this->invokeMethod($instance, 'handleProcessFinish', $exportData, $process);
    }

    /**
     * @throws ReflectionException
     */
    public function testHandleProcessFinishWithError(): void
    {
        $errorOutput = 'abc';
        $iconId = 'def';
        $icon = new Icon();
        $icon->id = $iconId;

        $process = $this->createMock(RenderIconProcess::class);
        $process->expects($this->once())
                ->method('isSuccessful')
                ->willReturn(false);
        $process->expects($this->any())
                ->method('getIcon')
                ->willReturn($icon);
        $process->expects($this->once())
                ->method('getErrorOutput')
                ->willReturn($errorOutput);

        $exportData = $this->createMock(ExportData::class);
        $exportData->expects($this->never())
                   ->method('getRenderedIcons');

        $this->progressBar->expects($this->once())
                          ->method('finish')
                          ->with($this->identicalTo($iconId));

        $this->errorOutput->expects($this->once())
                          ->method('write')
                          ->with($this->identicalTo($errorOutput));

        $instance = $this->createInstance();
        $this->invokeMethod($instance, 'handleProcessFinish', $exportData, $process);
    }
}
