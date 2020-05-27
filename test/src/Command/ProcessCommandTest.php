<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Command;

use BluePsyduck\TestHelper\ReflectionTrait;
use Exception;
use FactorioItemBrowser\Export\Command\ProcessCommand;
use FactorioItemBrowser\Export\Command\ProcessStep\ProcessStepInterface;
use FactorioItemBrowser\Export\Console\Console;
use FactorioItemBrowser\Export\Constant\CommandName;
use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\Entity\ProcessStepData;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Exception\InternalException;
use FactorioItemBrowser\ExportData\ExportData;
use FactorioItemBrowser\ExportData\ExportDataService;
use FactorioItemBrowser\ExportQueue\Client\Client\Facade;
use FactorioItemBrowser\ExportQueue\Client\Constant\JobStatus;
use FactorioItemBrowser\ExportQueue\Client\Constant\ListOrder;
use FactorioItemBrowser\ExportQueue\Client\Entity\Job;
use FactorioItemBrowser\ExportQueue\Client\Exception\ClientException;
use FactorioItemBrowser\ExportQueue\Client\Request\Job\ListRequest;
use FactorioItemBrowser\ExportQueue\Client\Request\Job\UpdateRequest;
use FactorioItemBrowser\ExportQueue\Client\Response\Job\DetailsResponse;
use FactorioItemBrowser\ExportQueue\Client\Response\Job\ListResponse;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The PHPUnit test of the ProcessCommand class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Command\ProcessCommand
 */
class ProcessCommandTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked console.
     * @var Console&MockObject
     */
    protected $console;

    /**
     * The mocked export data service.
     * @var ExportDataService&MockObject
     */
    protected $exportDataService;

    /**
     * The mocked export queue facade.
     * @var Facade&MockObject
     */
    protected $exportQueueFacade;

    /**
     * Sets up the test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->console = $this->createMock(Console::class);
        $this->exportDataService = $this->createMock(ExportDataService::class);
        $this->exportQueueFacade = $this->createMock(Facade::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $processSteps = [
            $this->createMock(ProcessStepInterface::class),
            $this->createMock(ProcessStepInterface::class),
        ];

        $command = new ProcessCommand(
            $this->console,
            $this->exportDataService,
            $this->exportQueueFacade,
            $processSteps
        );

        $this->assertSame($this->console, $this->extractProperty($command, 'console'));
        $this->assertSame($this->exportDataService, $this->extractProperty($command, 'exportDataService'));
        $this->assertSame($this->exportQueueFacade, $this->extractProperty($command, 'exportQueueFacade'));
        $this->assertSame($processSteps, $this->extractProperty($command, 'processSteps'));
    }

    /**
     * Tests the configure method.
     * @throws ReflectionException
     * @covers ::configure
     */
    public function testConfigure(): void
    {
        /* @var ProcessCommand&MockObject $command */
        $command = $this->getMockBuilder(ProcessCommand::class)
                        ->onlyMethods(['setName', 'setDescription'])
                        ->setConstructorArgs([
                            $this->console,
                            $this->exportDataService,
                            $this->exportQueueFacade,
                            []
                        ])
                        ->getMock();
        $command->expects($this->once())
                ->method('setName')
                ->with($this->identicalTo(CommandName::PROCESS));
        $command->expects($this->once())
                ->method('setDescription')
                ->with($this->isType('string'));

        $this->invokeMethod($command, 'configure');
    }

    /**
     * Tests the execute method.
     * @throws ReflectionException
     * @covers ::execute
     */
    public function testExecute(): void
    {
        /* @var Job&MockObject $exportJob */
        $exportJob = $this->createMock(Job::class);
        /* @var InputInterface&MockObject $input */
        $input = $this->createMock(InputInterface::class);
        /* @var OutputInterface&MockObject $output */
        $output = $this->createMock(OutputInterface::class);

        /* @var ProcessCommand&MockObject $command */
        $command = $this->getMockBuilder(ProcessCommand::class)
                        ->onlyMethods(['fetchExportJob', 'runExportJob'])
                        ->setConstructorArgs([$this->console, $this->exportDataService, $this->exportQueueFacade, []])
                        ->getMock();
        $command->expects($this->once())
                ->method('fetchExportJob')
                ->willReturn($exportJob);
        $command->expects($this->once())
                ->method('ruNExportJob')
                ->with($this->identicalTo($exportJob));

        $result = $this->invokeMethod($command, 'execute', $input, $output);

        $this->assertSame(0, $result);
    }
    
    /**
     * Tests the execute method.
     * @throws ReflectionException
     * @covers ::execute
     */
    public function testExecuteWithoutJob(): void
    {
        /* @var InputInterface&MockObject $input */
        $input = $this->createMock(InputInterface::class);
        /* @var OutputInterface&MockObject $output */
        $output = $this->createMock(OutputInterface::class);

        /* @var ProcessCommand&MockObject $command */
        $command = $this->getMockBuilder(ProcessCommand::class)
                        ->onlyMethods(['fetchExportJob', 'runExportJob'])
                        ->setConstructorArgs([$this->console, $this->exportDataService, $this->exportQueueFacade, []])
                        ->getMock();
        $command->expects($this->once())
                ->method('fetchExportJob')
                ->willReturn(null);
        $command->expects($this->never())
                ->method('ruNExportJob');
        
        $this->console->expects($this->once())
                      ->method('writeMessage')
                      ->with($this->identicalTo('No export job to process. Done.'));

        $result = $this->invokeMethod($command, 'execute', $input, $output);

        $this->assertSame(0, $result);
    }

    /**
     * Tests the execute method.
     * @throws ReflectionException
     * @covers ::execute
     */
    public function testExecuteWithException(): void
    {
        /* @var Job&MockObject $exportJob */
        $exportJob = $this->createMock(Job::class);
        /* @var InputInterface&MockObject $input */
        $input = $this->createMock(InputInterface::class);
        /* @var OutputInterface&MockObject $output */
        $output = $this->createMock(OutputInterface::class);
        /* @var Exception&MockObject $exception */
        $exception = $this->createMock(Exception::class);

        /* @var ProcessCommand&MockObject $command */
        $command = $this->getMockBuilder(ProcessCommand::class)
                        ->onlyMethods(['fetchExportJob', 'runExportJob'])
                        ->setConstructorArgs([$this->console, $this->exportDataService, $this->exportQueueFacade, []])
                        ->getMock();
        $command->expects($this->once())
                ->method('fetchExportJob')
                ->willReturn($exportJob);
        $command->expects($this->once())
                ->method('ruNExportJob')
                ->with($this->identicalTo($exportJob))
                ->willThrowException($exception);

        $this->console->expects($this->once())
                      ->method('writeException')
                      ->with($this->identicalTo($exception));

        $result = $this->invokeMethod($command, 'execute', $input, $output);

        $this->assertSame(1, $result);
    }

    /**
     * Tests the fetchExportJob method.
     * @throws ReflectionException
     * @covers ::fetchExportJob
     */
    public function testFetchExportJob(): void
    {
        /* @var Job&MockObject $job */
        $job = $this->createMock(Job::class);

        /* @var ListResponse&MockObject $response */
        $response = $this->createMock(ListResponse::class);
        $response->expects($this->once())
                 ->method('getJobs')
                 ->willReturn([$job]);

        $expectedRequest = new ListRequest();
        $expectedRequest->setStatus(JobStatus::QUEUED)
                        ->setOrder(ListOrder::PRIORITY)
                        ->setLimit(1);

        $this->console->expects($this->once())
                      ->method('writeAction')
                      ->with($this->identicalTo('Fetching export job from queue'));

        $this->exportQueueFacade->expects($this->once())
                                ->method('getJobList')
                                ->with($this->equalTo($expectedRequest))
                                ->willReturn($response);

        $command = new ProcessCommand($this->console, $this->exportDataService, $this->exportQueueFacade, []);
        $result = $this->invokeMethod($command, 'fetchExportJob');

        $this->assertSame($job, $result);
    }

    /**
     * Tests the fetchExportJob method.
     * @throws ReflectionException
     * @covers ::fetchExportJob
     */
    public function testFetchExportJobWithoutJob(): void
    {
        /* @var ListResponse&MockObject $response */
        $response = $this->createMock(ListResponse::class);
        $response->expects($this->once())
                 ->method('getJobs')
                 ->willReturn([]);

        $expectedRequest = new ListRequest();
        $expectedRequest->setStatus(JobStatus::QUEUED)
                        ->setOrder(ListOrder::PRIORITY)
                        ->setLimit(1);

        $this->console->expects($this->once())
                      ->method('writeAction')
                      ->with($this->identicalTo('Fetching export job from queue'));

        $this->exportQueueFacade->expects($this->once())
                                ->method('getJobList')
                                ->with($this->equalTo($expectedRequest))
                                ->willReturn($response);

        $command = new ProcessCommand($this->console, $this->exportDataService, $this->exportQueueFacade, []);
        $result = $this->invokeMethod($command, 'fetchExportJob');

        $this->assertNull($result);
    }

    /**
     * Tests the fetchExportJob method.
     * @throws ReflectionException
     * @covers ::fetchExportJob
     */
    public function testFetchExportJobWithException(): void
    {
        $expectedRequest = new ListRequest();
        $expectedRequest->setStatus(JobStatus::QUEUED)
                        ->setOrder(ListOrder::PRIORITY)
                        ->setLimit(1);

        $this->console->expects($this->once())
                      ->method('writeAction')
                      ->with($this->identicalTo('Fetching export job from queue'));

        $this->exportQueueFacade->expects($this->once())
                                ->method('getJobList')
                                ->with($this->equalTo($expectedRequest))
                                ->willThrowException($this->createMock(ClientException::class));

        $this->expectException(InternalException::class);

        $command = new ProcessCommand($this->console, $this->exportDataService, $this->exportQueueFacade, []);
        $this->invokeMethod($command, 'fetchExportJob');
    }

    /**
     * Tests the runExportJob method.
     * @throws ReflectionException
     * @covers ::runExportJob
     */
    public function testRunExportJob(): void
    {
        $combinationId = 'abc';

        /* @var ProcessStepInterface&MockObject $processStep1 */
        $processStep1 = $this->createMock(ProcessStepInterface::class);
        /* @var ProcessStepInterface&MockObject $processStep2 */
        $processStep2 = $this->createMock(ProcessStepInterface::class);
        /* @var ProcessStepData&MockObject $processStepData */
        $processStepData = $this->createMock(ProcessStepData::class);

        /* @var Job&MockObject $exportJob */
        $exportJob = $this->createMock(Job::class);
        $exportJob->expects($this->once())
                  ->method('getCombinationId')
                  ->willReturn($combinationId);

        $this->console->expects($this->once())
                      ->method('writeHeadline')
                      ->with($this->identicalTo('Processing combination abc'));

        /* @var ProcessCommand&MockObject $command */
        $command = $this->getMockBuilder(ProcessCommand::class)
                        ->onlyMethods(['createProcessStepData', 'runProcessStep'])
                        ->setConstructorArgs([
                            $this->console,
                            $this->exportDataService,
                            $this->exportQueueFacade,
                            [$processStep1, $processStep2],
                        ])
                        ->getMock();
        $command->expects($this->once())
                ->method('createProcessStepData')
                ->with($this->identicalTo($exportJob))
                ->willReturn($processStepData);
        $command->expects($this->exactly(2))
                ->method('runProcessStep')
                ->withConsecutive(
                    [$this->identicalTo($processStep1), $this->identicalTo($processStepData)],
                    [$this->identicalTo($processStep2), $this->identicalTo($processStepData)]
                );

        $this->invokeMethod($command, 'runExportJob', $exportJob);
    }

    /**
     * Tests the createProcessStepData method.
     * @throws ReflectionException
     * @covers ::createProcessStepData
     */
    public function testCreateProcessStepData(): void
    {
        $combinationId = 'abc';

        /* @var ExportData&MockObject $exportData */
        $exportData = $this->createMock(ExportData::class);

        /* @var Job&MockObject $exportJob */
        $exportJob = $this->createMock(Job::class);
        $exportJob->expects($this->once())
                  ->method('getCombinationId')
                  ->willReturn($combinationId);

        $expectedResult = new ProcessStepData();
        $expectedResult->setExportJob($exportJob)
                       ->setExportData($exportData)
                       ->setDump(new Dump());

        $this->exportDataService->expects($this->once())
                                ->method('createExport')
                                ->with($this->identicalTo($combinationId))
                                ->willReturn($exportData);

        $command = new ProcessCommand($this->console, $this->exportDataService, $this->exportQueueFacade, []);
        $result = $this->invokeMethod($command, 'createProcessStepData', $exportJob);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the runProcessStep method.
     * @throws ReflectionException
     * @covers ::runProcessStep
     */
    public function testRunProcessStep(): void
    {
        $label = 'abc';
        $status = 'def';

        /* @var Job&MockObject $exportJob1 */
        $exportJob1 = $this->createMock(Job::class);
        /* @var Job&MockObject $exportJob2 */
        $exportJob2 = $this->createMock(Job::class);

        /* @var ProcessStepData&MockObject $processStepData */
        $processStepData = $this->createMock(ProcessStepData::class);
        $processStepData->expects($this->once())
                        ->method('getExportJob')
                        ->willReturn($exportJob1);
        $processStepData->expects($this->once())
                        ->method('setExportJob')
                        ->with($this->identicalTo($exportJob2));

        /* @var ProcessStepInterface&MockObject $processStep */
        $processStep = $this->createMock(ProcessStepInterface::class);
        $processStep->expects($this->once())
                    ->method('getLabel')
                    ->willReturn($label);
        $processStep->expects($this->once())
                    ->method('getExportJobStatus')
                    ->willReturn($status);
        $processStep->expects($this->once())
                    ->method('run')
                    ->with($this->identicalTo($processStepData));

        $this->console->expects($this->once())
                      ->method('writeStep')
                      ->with($this->identicalTo($label));

        /* @var ProcessCommand&MockObject $command */
        $command = $this->getMockBuilder(ProcessCommand::class)
                        ->onlyMethods(['updateExportJob'])
                        ->setConstructorArgs([$this->console, $this->exportDataService, $this->exportQueueFacade, []])
                        ->getMock();
        $command->expects($this->once())
                ->method('updateExportJob')
                ->with($this->identicalTo($exportJob1), $this->identicalTo($status))
                ->willReturn($exportJob2);

        $this->invokeMethod($command, 'runProcessStep', $processStep, $processStepData);
    }

    /**
     * Tests the runProcessStep method.
     * @throws ReflectionException
     * @covers ::runProcessStep
     */
    public function testRunProcessStepWithException(): void
    {
        $label = 'abc';
        $status = 'def';

        $exception = new ExportException('ghi');
        $expectedErrorMessage = 'ExportException: ghi';

        /* @var Job&MockObject $exportJob1 */
        $exportJob1 = $this->createMock(Job::class);
        /* @var Job&MockObject $exportJob2 */
        $exportJob2 = $this->createMock(Job::class);

        /* @var ProcessStepData&MockObject $processStepData */
        $processStepData = $this->createMock(ProcessStepData::class);
        $processStepData->expects($this->exactly(2))
                        ->method('getExportJob')
                        ->willReturnOnConsecutiveCalls(
                            $exportJob1,
                            $exportJob2
                        );
        $processStepData->expects($this->once())
                        ->method('setExportJob')
                        ->with($this->identicalTo($exportJob2));

        /* @var ProcessStepInterface&MockObject $processStep */
        $processStep = $this->createMock(ProcessStepInterface::class);
        $processStep->expects($this->once())
                    ->method('getLabel')
                    ->willReturn($label);
        $processStep->expects($this->once())
                    ->method('getExportJobStatus')
                    ->willReturn($status);
        $processStep->expects($this->once())
                    ->method('run')
                    ->with($this->identicalTo($processStepData))
                    ->willThrowException($exception);

        $this->console->expects($this->once())
                      ->method('writeStep')
                      ->with($this->identicalTo($label));

        $this->expectExceptionObject($exception);

        /* @var ProcessCommand&MockObject $command */
        $command = $this->getMockBuilder(ProcessCommand::class)
                        ->onlyMethods(['updateExportJob'])
                        ->setConstructorArgs([$this->console, $this->exportDataService, $this->exportQueueFacade, []])
                        ->getMock();
        $command->expects($this->exactly(2))
                ->method('updateExportJob')
                ->withConsecutive(
                    [$this->identicalTo($exportJob1), $this->identicalTo($status)],
                    [
                        $this->identicalTo($exportJob2),
                        $this->identicalTo(JobStatus::ERROR),
                        $this->identicalTo($expectedErrorMessage),
                    ]
                )
                ->willReturn($exportJob2);

        $this->invokeMethod($command, 'runProcessStep', $processStep, $processStepData);
    }

    /**
     * Tests the updateExportJob method.
     * @throws ReflectionException
     * @covers ::updateExportJob
     */
    public function testUpdateExportJob(): void
    {
        $status = 'abc';
        $errorMessage = 'def';

        $exportJob = new Job();
        $exportJob->setId('ghi')
                  ->setStatus('jkl');

        $expectedRequest = new UpdateRequest();
        $expectedRequest->setJobId('ghi')
                        ->setStatus('abc')
                        ->setErrorMessage('def');

        /* @var DetailsResponse&MockObject $response */
        $response = $this->createMock(DetailsResponse::class);

        $this->exportQueueFacade->expects($this->once())
                                ->method('updateJob')
                                ->with($this->equalTo($expectedRequest))
                                ->willReturn($response);

        $command = new ProcessCommand($this->console, $this->exportDataService, $this->exportQueueFacade, []);
        $result = $this->invokeMethod($command, 'updateExportJob', $exportJob, $status, $errorMessage);

        $this->assertSame($response, $result);
    }

    /**
     * Tests the updateExportJob method.
     * @throws ReflectionException
     * @covers ::updateExportJob
     */
    public function testUpdateExportJobWithSameStatus(): void
    {
        $status = 'abc';
        $errorMessage = 'def';

        $exportJob = new Job();
        $exportJob->setId('ghi')
                  ->setStatus('abc');

        $this->exportQueueFacade->expects($this->never())
                                ->method('updateJob');

        $command = new ProcessCommand($this->console, $this->exportDataService, $this->exportQueueFacade, []);
        $result = $this->invokeMethod($command, 'updateExportJob', $exportJob, $status, $errorMessage);

        $this->assertSame($exportJob, $result);
    }
}
