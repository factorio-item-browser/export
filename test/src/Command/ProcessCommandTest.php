<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Command;

use BluePsyduck\TestHelper\ReflectionTrait;
use Exception;
use FactorioItemBrowser\CombinationApi\Client\ClientInterface;
use FactorioItemBrowser\CombinationApi\Client\Constant\JobStatus;
use FactorioItemBrowser\CombinationApi\Client\Constant\ListOrder;
use FactorioItemBrowser\CombinationApi\Client\Exception\ClientException;
use FactorioItemBrowser\CombinationApi\Client\Request\Combination\StatusRequest;
use FactorioItemBrowser\CombinationApi\Client\Request\Job\ListRequest;
use FactorioItemBrowser\CombinationApi\Client\Request\Job\UpdateRequest;
use FactorioItemBrowser\CombinationApi\Client\Response\Combination\StatusResponse;
use FactorioItemBrowser\CombinationApi\Client\Response\Job\DetailsResponse;
use FactorioItemBrowser\CombinationApi\Client\Response\Job\ListResponse;
use FactorioItemBrowser\CombinationApi\Client\Transfer\Combination;
use FactorioItemBrowser\CombinationApi\Client\Transfer\Job;
use FactorioItemBrowser\Export\Command\ProcessCommand;
use FactorioItemBrowser\Export\Command\ProcessStep\ProcessStepInterface;
use FactorioItemBrowser\Export\Output\Console;
use FactorioItemBrowser\Export\Constant\CommandName;
use FactorioItemBrowser\Export\Entity\ProcessStepData;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Exception\InternalException;
use FactorioItemBrowser\ExportData\ExportData;
use FactorioItemBrowser\ExportData\ExportDataService;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\RejectedPromise;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
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

    /** @var ClientInterface&MockObject */
    private ClientInterface $combinationApiClient;
    /** @var Console&MockObject */
    private Console $console;
    /** @var ExportDataService&MockObject */
    private ExportDataService $exportDataService;
    /** @var LoggerInterface&MockObject */
    private LoggerInterface $logger;
    /** @var array<ProcessStepInterface> */
    private array $processSteps = [];

    protected function setUp(): void
    {
        $this->combinationApiClient = $this->createMock(ClientInterface::class);
        $this->console = $this->createMock(Console::class);
        $this->exportDataService = $this->createMock(ExportDataService::class);
        $this->logger = $this->createMock(LoggerInterface::class);
    }

    /**
     * @param array<string> $mockedMethods
     * @return ProcessCommand&MockObject
     */
    private function createInstance(array $mockedMethods = []): ProcessCommand
    {
        return $this->getMockBuilder(ProcessCommand::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($mockedMethods)
                    ->setConstructorArgs([
                        $this->combinationApiClient,
                        $this->console,
                        $this->exportDataService,
                        $this->logger,
                        $this->processSteps,
                    ])
                    ->getMock();
    }

    /**
     * @throws ReflectionException
     */
    public function testConfigure(): void
    {
        $instance = $this->createInstance(['setName', 'setDescription']);
        $instance->expects($this->once())
                 ->method('setName')
                 ->with($this->identicalTo(CommandName::PROCESS));
        $instance->expects($this->once())
                 ->method('setDescription')
                 ->with($this->isType('string'));

        $this->invokeMethod($instance, 'configure');
    }

    /**
     * @throws ReflectionException
     */
    public function testExecute(): void
    {
        $exportJob = $this->createMock(Job::class);
        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);

        $instance = $this->createInstance(['fetchExportJob', 'runExportJob']);
        $instance->expects($this->once())
                 ->method('fetchExportJob')
                 ->willReturn($exportJob);
        $instance->expects($this->once())
                 ->method('ruNExportJob')
                 ->with($this->identicalTo($exportJob));

        $result = $this->invokeMethod($instance, 'execute', $input, $output);

        $this->assertSame(0, $result);
    }

    /**
     * @throws ReflectionException
     */
    public function testExecuteWithoutJob(): void
    {
        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);

        $instance = $this->createInstance(['fetchExportJob', 'runExportJob']);
        $instance->expects($this->once())
                 ->method('fetchExportJob')
                 ->willReturn(null);
        $instance->expects($this->never())
                 ->method('ruNExportJob');

        $result = $this->invokeMethod($instance, 'execute', $input, $output);

        $this->assertSame(0, $result);
    }

    /**
     * @throws ReflectionException
     */
    public function testExecuteWithException(): void
    {
        $exportJob = $this->createMock(Job::class);
        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);
        $exception = $this->createMock(Exception::class);

        $this->console->expects($this->once())
                      ->method('writeException')
                      ->with($this->identicalTo($exception));

        $instance = $this->createInstance(['fetchExportJob', 'runExportJob']);
        $instance->expects($this->once())
                ->method('fetchExportJob')
                ->willReturn($exportJob);
        $instance->expects($this->once())
                ->method('ruNExportJob')
                ->with($this->identicalTo($exportJob))
                ->willThrowException($exception);

        $result = $this->invokeMethod($instance, 'execute', $input, $output);

        $this->assertSame(1, $result);
    }

    /**
     * @throws ReflectionException
     */
    public function testFetchExportJob(): void
    {
        $job = $this->createMock(Job::class);

        $response = new ListResponse();
        $response->jobs = [$job];

        $expectedRequest = new ListRequest();
        $expectedRequest->status = JobStatus::QUEUED;
        $expectedRequest->order = ListOrder::PRIORITY;
        $expectedRequest->limit = 1;

        $this->combinationApiClient->expects($this->once())
                                   ->method('sendRequest')
                                   ->with($this->equalTo($expectedRequest))
                                   ->willReturn(new FulfilledPromise($response));

        $this->console->expects($this->once())
                      ->method('writeAction')
                      ->with($this->identicalTo('Fetching export job from queue'));

        $instance = $this->createInstance();
        $result = $this->invokeMethod($instance, 'fetchExportJob');

        $this->assertSame($job, $result);
    }

    /**
     * @throws ReflectionException
     */
    public function testFetchExportJobWithoutJob(): void
    {
        $response = new ListResponse();

        $expectedRequest = new ListRequest();
        $expectedRequest->status = JobStatus::QUEUED;
        $expectedRequest->order = ListOrder::PRIORITY;
        $expectedRequest->limit = 1;

        $this->combinationApiClient->expects($this->once())
                                   ->method('sendRequest')
                                   ->with($this->equalTo($expectedRequest))
                                   ->willReturn(new FulfilledPromise($response));

        $this->console->expects($this->once())
                      ->method('writeAction')
                      ->with($this->identicalTo('Fetching export job from queue'));

        $instance = $this->createInstance();
        $result = $this->invokeMethod($instance, 'fetchExportJob');

        $this->assertNull($result);
    }

    /**
     * @throws ReflectionException
     */
    public function testFetchExportJobWithException(): void
    {
        $expectedRequest = new ListRequest();
        $expectedRequest->status = JobStatus::QUEUED;
        $expectedRequest->order = ListOrder::PRIORITY;
        $expectedRequest->limit = 1;

        $this->combinationApiClient->expects($this->once())
                                   ->method('sendRequest')
                                   ->with($this->equalTo($expectedRequest))
                                   ->willReturn(new RejectedPromise($this->createMock(ClientException::class)));

        $this->console->expects($this->once())
                      ->method('writeAction')
                      ->with($this->identicalTo('Fetching export job from queue'));

        $this->expectException(InternalException::class);

        $instance = $this->createInstance([]);
        $this->invokeMethod($instance, 'fetchExportJob');
    }

    /**
     * @throws ReflectionException
     */
    public function testRunExportJob(): void
    {
        $combinationId = 'abc';

        $processStep1 = $this->createMock(ProcessStepInterface::class);
        $processStep2 = $this->createMock(ProcessStepInterface::class);
        $processStepData = $this->createMock(ProcessStepData::class);

        $exportJob = new Job();
        $exportJob->combinationId = $combinationId;

        $this->console->expects($this->once())
                      ->method('writeHeadline')
                      ->with($this->identicalTo('Processing combination abc'));

        $this->processSteps = [$processStep1, $processStep2];

        $instance = $this->createInstance(['createProcessStepData', 'runProcessStep']);
        $instance->expects($this->once())
                 ->method('createProcessStepData')
                 ->with($this->identicalTo($exportJob))
                 ->willReturn($processStepData);
        $instance->expects($this->exactly(2))
                 ->method('runProcessStep')
                 ->withConsecutive(
                     [$this->identicalTo($processStep1), $this->identicalTo($processStepData)],
                     [$this->identicalTo($processStep2), $this->identicalTo($processStepData)]
                 );

        $this->invokeMethod($instance, 'runExportJob', $exportJob);
    }

    /**
     * @throws ReflectionException
     */
    public function testCreateProcessStepData(): void
    {
        $combinationId = 'abc';
        $exportData = $this->createMock(ExportData::class);

        $exportJob = new Job();
        $exportJob->combinationId = $combinationId;

        $combination = $this->createMock(Combination::class);

        $expectedResult = new ProcessStepData();
        $expectedResult->combination = $combination;
        $expectedResult->exportJob = $exportJob;
        $expectedResult->exportData = $exportData;

        $this->exportDataService->expects($this->once())
                                ->method('createExport')
                                ->with($this->identicalTo($combinationId))
                                ->willReturn($exportData);

        $instance = $this->createInstance(['fetchCombination']);
        $instance->expects($this->once())
                 ->method('fetchCombination')
                 ->with($this->identicalTo($combinationId))
                 ->willReturn($combination);

        $result = $this->invokeMethod($instance, 'createProcessStepData', $exportJob);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @throws ReflectionException
     */
    public function testFetchCombination(): void
    {
        $combinationId = 'abc';
        $response = $this->createMock(StatusResponse::class);

        $expectedRequest = new StatusRequest();
        $expectedRequest->combinationId = $combinationId;

        $this->combinationApiClient->expects($this->once())
                                   ->method('sendRequest')
                                   ->with($this->equalTo($expectedRequest))
                                   ->willReturn(new FulfilledPromise($response));

        $instance = $this->createInstance();
        $result = $this->invokeMethod($instance, 'fetchCombination', $combinationId);

        $this->assertSame($response, $result);
    }

    /**
     * @throws ReflectionException
     */
    public function testFetchCombinationWithException(): void
    {
        $combinationId = 'abc';

        $expectedRequest = new StatusRequest();
        $expectedRequest->combinationId = $combinationId;

        $this->combinationApiClient->expects($this->once())
                                   ->method('sendRequest')
                                   ->with($this->equalTo($expectedRequest))
                                   ->willThrowException($this->createMock(ClientException::class));

        $this->expectException(InternalException::class);

        $instance = $this->createInstance();
        $this->invokeMethod($instance, 'fetchCombination', $combinationId);
    }

    /**
     * @throws ReflectionException
     */
    public function testRunProcessStep(): void
    {
        $label = 'abc';
        $status = 'def';

        $exportJob1 = $this->createMock(Job::class);
        $exportJob2 = $this->createMock(Job::class);

        $processStepData = new ProcessStepData();
        $processStepData->combination = new Combination();
        $processStepData->exportJob = $exportJob1;

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

        $instance = $this->createInstance(['updateExportJob']);
        $instance->expects($this->once())
                 ->method('updateExportJob')
                 ->with($this->identicalTo($exportJob1), $this->identicalTo($status))
                 ->willReturn($exportJob2);

        $this->invokeMethod($instance, 'runProcessStep', $processStep, $processStepData);

        $this->assertSame($exportJob2, $processStepData->exportJob);
    }

    /**
     * @throws ReflectionException
     */
    public function testRunProcessStepWithException(): void
    {
        $label = 'abc';
        $status = 'def';

        $exception = new ExportException('ghi');
        $expectedErrorMessage = 'ExportException: ghi';

        $exportJob1 = $this->createMock(Job::class);
        $exportJob2 = $this->createMock(Job::class);

        $processStepData = new ProcessStepData();
        $processStepData->combination = new Combination();
        $processStepData->exportJob = $exportJob1;

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

        $instance = $this->createInstance(['updateExportJob']);
        $instance->expects($this->exactly(2))
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

        $this->invokeMethod($instance, 'runProcessStep', $processStep, $processStepData);
    }

    /**
     * @throws ReflectionException
     */
    public function testUpdateExportJob(): void
    {
        $status = 'abc';
        $errorMessage = 'def';

        $exportJob = new Job();
        $exportJob->id = 'ghi';
        $exportJob->status = 'jkl';

        $expectedRequest = new UpdateRequest();
        $expectedRequest->id = 'ghi';
        $expectedRequest->status = 'abc';
        $expectedRequest->errorMessage = 'def';

        $response = $this->createMock(DetailsResponse::class);

        $this->combinationApiClient->expects($this->once())
                                   ->method('sendRequest')
                                   ->with($this->equalTo($expectedRequest))
                                   ->willReturn(new FulfilledPromise($response));

        $instance = $this->createInstance();
        $result = $this->invokeMethod($instance, 'updateExportJob', $exportJob, $status, $errorMessage);

        $this->assertSame($response, $result);
    }

    /**
     * @throws ReflectionException
     */
    public function testUpdateExportJobWithSameStatus(): void
    {
        $status = 'abc';
        $errorMessage = 'def';

        $exportJob = new Job();
        $exportJob->id = 'ghi';
        $exportJob->status = 'abc';

        $this->combinationApiClient->expects($this->never())
                                   ->method('sendRequest');

        $instance = $this->createInstance();
        $result = $this->invokeMethod($instance, 'updateExportJob', $exportJob, $status, $errorMessage);

        $this->assertSame($exportJob, $result);
    }
}
