<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command;

use Exception;
use FactorioItemBrowser\Export\Command\ProcessStep\ProcessStepInterface;
use FactorioItemBrowser\Export\Console\Console;
use FactorioItemBrowser\Export\Constant\CommandName;
use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\Entity\ProcessStepData;
use FactorioItemBrowser\Export\Exception\InternalException;
use FactorioItemBrowser\ExportData\ExportDataService;
use FactorioItemBrowser\ExportQueue\Client\Client\Facade;
use FactorioItemBrowser\ExportQueue\Client\Constant\JobStatus;
use FactorioItemBrowser\ExportQueue\Client\Constant\ListOrder;
use FactorioItemBrowser\ExportQueue\Client\Entity\Job;
use FactorioItemBrowser\ExportQueue\Client\Exception\ClientException;
use FactorioItemBrowser\ExportQueue\Client\Request\Job\ListRequest;
use FactorioItemBrowser\ExportQueue\Client\Request\Job\UpdateRequest;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The command for processing the next job in the import queue.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ProcessCommand extends Command
{
    protected Console $console;
    protected ExportDataService $exportDataService;
    protected Facade $exportQueueFacade;
    protected LoggerInterface $logger;
    /** @var array<ProcessStepInterface>  */
    protected array $processSteps;

    /**
     * @param Console $console
     * @param ExportDataService $exportDataService
     * @param Facade $exportQueueFacade
     * @param LoggerInterface $logger
     * @param array<ProcessStepInterface> $exportProcessSteps
     */
    public function __construct(
        Console $console,
        ExportDataService $exportDataService,
        Facade $exportQueueFacade,
        LoggerInterface $logger,
        array $exportProcessSteps
    ) {
        parent::__construct();

        $this->console = $console;
        $this->exportDataService = $exportDataService;
        $this->exportQueueFacade = $exportQueueFacade;
        $this->logger = $logger;
        $this->processSteps = $exportProcessSteps;
    }

    /**
     * Configures the command.
     */
    protected function configure(): void
    {
        parent::configure();

        $this->setName(CommandName::PROCESS);
        $this->setDescription('Processes the next jop scheduled to be exported.');
    }

    /**
     * Executes the command.
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $exportJob = $this->fetchExportJob();
            if ($exportJob === null) {
                $this->console->writeMessage('No export job to process. Done.');
                $this->logger->info('No export job to process.');
                return 0;
            }

            $this->runExportJob($exportJob);
            return 0;
        } catch (Exception $e) {
            $this->console->writeException($e);
            return 1;
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
                ->setOrder(ListOrder::PRIORITY)
                ->setLimit(1);

        try {
            $response = $this->exportQueueFacade->getJobList($request);
            return $response->getJobs()[0] ?? null;
        } catch (ClientException $e) {
            throw new InternalException(sprintf('Failed to fetch export job from queue: %s', $e->getMessage()), $e);
        }
    }

    /**
     * Runs the specified export job.
     * @param Job $exportJob
     * @throws Exception
     */
    protected function runExportJob(Job $exportJob): void
    {
        $this->logger->info('Processing export job', ['combination' => $exportJob->getCombinationId()]);
        $this->console->writeHeadline(sprintf('Processing combination %s', $exportJob->getCombinationId()));
        $processStepData = $this->createProcessStepData($exportJob);

        foreach ($this->processSteps as $processStep) {
            $this->runProcessStep($processStep, $processStepData);
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
        $result->exportJob = $exportJob;
        $result->exportData = $exportData;
        $result->dump = new Dump();
        return $result;
    }

    /**
     * Runs the processing step.
     * @param ProcessStepInterface $step
     * @param ProcessStepData $data
     * @throws Exception
     */
    protected function runProcessStep(ProcessStepInterface $step, ProcessStepData $data): void
    {
        $this->console->writeStep($step->getLabel());
        $data->exportJob = $this->updateExportJob($data->exportJob, $step->getExportJobStatus());

        try {
            $step->run($data);
        } catch (Exception $e) {
            $exceptionClass = substr((string) strrchr(get_class($e), '\\'), 1);
            $exceptionMessage = $e->getMessage();

            $this->logger->error($exceptionMessage, [
                'class' => $exceptionClass,
                'combination' => $data->exportJob->getCombinationId(),
            ]);
            $this->updateExportJob(
                $data->exportJob,
                JobStatus::ERROR,
                sprintf('%s: %s', $exceptionClass, $exceptionMessage)
            );
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
