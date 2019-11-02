<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command;

use Exception;
use FactorioItemBrowser\Export\Command\ProcessStep\ProcessStepInterface;
use FactorioItemBrowser\Export\Console\Console;
use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\Entity\ProcessStepData;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Exception\InternalException;
use FactorioItemBrowser\ExportData\ExportDataService;
use FactorioItemBrowser\ExportQueue\Client\Client\Facade;
use FactorioItemBrowser\ExportQueue\Client\Constant\JobStatus;
use FactorioItemBrowser\ExportQueue\Client\Entity\Job;
use FactorioItemBrowser\ExportQueue\Client\Exception\ClientException;
use FactorioItemBrowser\ExportQueue\Client\Request\Job\ListRequest;
use FactorioItemBrowser\ExportQueue\Client\Request\Job\UpdateRequest;
use Zend\Console\Adapter\AdapterInterface;
use ZF\Console\Route;

/**
 * The command for processing the next job in the import queue.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ProcessCommand implements CommandInterface
{
    /**
     * The export data service.
     * @var ExportDataService
     */
    protected $exportDataService;

    /**
     * The export queue facade.
     * @var Facade
     */
    protected $exportQueueFacade;

    /**
     * The process steps.
     * @var array|ProcessStepInterface[]
     */
    protected $processSteps;

    /**
     * The console.
     * @var Console
     */
    protected $console;

    /**
     * ProcessCommand constructor.
     * @param ExportDataService $exportDataService
     * @param Facade $exportQueueFacade
     * @param array|ProcessStepInterface[] $exportProcessSteps
     */
    public function __construct(
        ExportDataService $exportDataService,
        Facade $exportQueueFacade,
        array $exportProcessSteps
    ) {
        $this->exportDataService = $exportDataService;
        $this->exportQueueFacade = $exportQueueFacade;
        $this->processSteps = $exportProcessSteps;
    }

    /**
     * Invokes the command.
     * @param Route $route
     * @param AdapterInterface $consoleAdapter
     * @return int
     */
    public function __invoke(Route $route, AdapterInterface $consoleAdapter): int
    {
        $this->console = new Console($consoleAdapter);
        try {
            $this->execute();
            return 0;
        } catch (Exception $e) {
            $this->console->writeException($e);
            return 1;
        }
    }

    /**
     * Executes the command.
     * @throws ExportException
     * @throws Exception
     */
    protected function execute(): void
    {
        $exportJob = $this->fetchExportJob();
        if ($exportJob === null) {
            $this->console->writeMessage('No export job to process. Done.');
            return;
        }

        $this->console->writeHeadline('Processing combination %s', $exportJob->getCombinationId());
        $processStepData = $this->createProcessStepData($exportJob);

        foreach ($this->processSteps as $processStep) {
            $this->processStep($processStep, $processStepData);
        }
    }

    /**
     * Fetches the next export job to process from the queue.
     * @return Job|null
     * @throws InternalException
     */
    protected function fetchExportJob(): ?Job
    {
        $this->console->writeAction('Fetching export job from queue');

        $request = new ListRequest();
        $request->setStatus(JobStatus::QUEUED)
                ->setLimit(1);

        try {
            $response = $this->exportQueueFacade->getJobList($request);
            return $response->getJobs()[0] ?? null;
        } catch (ClientException $e) {
            throw new InternalException('Failed to fetch export job from queue.', $e);
        }
    }

    /**
     * Creates the instance of the process step data.
     * @param Job $exportJob
     * @return ProcessStepData
     */
    protected function createProcessStepData(Job $exportJob): ProcessStepData
    {
        $exportData = $this->exportDataService->createExport($exportJob->getCombinationId());

        $result = new ProcessStepData();
        $result->setExportJob($exportJob)
               ->setExportData($exportData)
               ->setDump(new Dump());
        return $result;
    }

    /**
     * Processes one step.
     * @param ProcessStepInterface $step
     * @param ProcessStepData $data
     * @throws Exception
     */
    protected function processStep(ProcessStepInterface $step, ProcessStepData $data): void
    {
        $this->console->writeStep($step->getLabel());
        $data->setExportJob($this->updateExportJob($data->getExportJob(), $step->getExportJobStatus()));

        try {
            $step->run($data);
        } catch (Exception $e) {
            $this->updateExportJob($data->getExportJob(), JobStatus::ERROR, $e->getMessage());
            throw $e;
        }
    }

    /**
     * Updates the export job in the queue.
     * @param Job $exportJob
     * @param string $status
     * @param string $errorMessage
     * @return Job
     * @throws ClientException
     */
    protected function updateExportJob(Job $exportJob, string $status, string $errorMessage = ''): Job
    {
        if ($exportJob->getStatus() === $status) {
            // We do not have to change the status at all.
            return $exportJob;
        }

        $request = new UpdateRequest();
        $request->setJobId($exportJob->getId())
                ->setStatus($status)
                ->setErrorMessage($errorMessage);

        return $this->exportQueueFacade->updateJob($request);
    }
}
