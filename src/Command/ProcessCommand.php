<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command;

use BluePsyduck\LaminasAutoWireFactory\Attribute\InjectAliasArray;
use Exception;
use FactorioItemBrowser\CombinationApi\Client\ClientInterface;
use FactorioItemBrowser\CombinationApi\Client\Constant\JobStatus;
use FactorioItemBrowser\CombinationApi\Client\Constant\ListOrder;
use FactorioItemBrowser\CombinationApi\Client\Exception\ClientException;
use FactorioItemBrowser\CombinationApi\Client\Request\Combination\StatusRequest;
use FactorioItemBrowser\CombinationApi\Client\Request\Job\ListRequest;
use FactorioItemBrowser\CombinationApi\Client\Request\Job\UpdateRequest;
use FactorioItemBrowser\CombinationApi\Client\Response\Job\ListResponse;
use FactorioItemBrowser\CombinationApi\Client\Transfer\Combination;
use FactorioItemBrowser\CombinationApi\Client\Transfer\Job;
use FactorioItemBrowser\Export\Command\ProcessStep\ProcessStepInterface;
use FactorioItemBrowser\Export\Constant\ConfigKey;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Output\Console;
use FactorioItemBrowser\Export\Constant\CommandName;
use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\Entity\ProcessStepData;
use FactorioItemBrowser\Export\Exception\InternalException;
use FactorioItemBrowser\ExportData\ExportDataService;
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
    /**
     * @param array<ProcessStepInterface> $processSteps
     */
    public function __construct(
        protected readonly ClientInterface $combinationApiClient,
        protected readonly Console $console,
        protected readonly ExportDataService $exportDataService,
        protected readonly LoggerInterface $logger,
        #[InjectAliasArray(ConfigKey::MAIN, ConfigKey::PROCESS_STEPS)]
        protected readonly array $processSteps
    ) {
        parent::__construct();
    }

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
        $request->status = JobStatus::QUEUED;
        $request->order = ListOrder::PRIORITY;
        $request->limit = 1;

        try {
            /** @var ListResponse $response */
            $response = $this->combinationApiClient->sendRequest($request)->wait();
            return $response->jobs[0] ?? null;
        } catch (ClientException $e) {
            throw new InternalException(
                sprintf('Failed to fetch export job from the Combination API: %s', $e->getMessage()),
                $e,
            );
        }
    }

    /**
     * Runs the specified export job.
     * @param Job $exportJob
     * @throws Exception
     */
    protected function runExportJob(Job $exportJob): void
    {
        $this->logger->info('Processing export job', ['combination' => $exportJob->combinationId]);
        $this->console->writeHeadline(sprintf('Processing combination %s', $exportJob->combinationId));
        $processStepData = $this->createProcessStepData($exportJob);

        foreach ($this->processSteps as $processStep) {
            $this->runProcessStep($processStep, $processStepData);
        }
    }

    /**
     * Creates the instance of the process step data.
     * @param Job $exportJob
     * @return ProcessStepData
     * @throws ExportException
     */
    protected function createProcessStepData(Job $exportJob): ProcessStepData
    {
        $result = new ProcessStepData();
        $result->exportJob = $exportJob;
        $result->combination = $this->fetchCombination($exportJob->combinationId);
        $result->exportData = $this->exportDataService->createExport($exportJob->combinationId);
        $result->dump = new Dump();
        return $result;
    }

    /**
     * Fetches the details of the combination.
     * @param string $combinationId
     * @return Combination
     * @throws ExportException
     */
    protected function fetchCombination(string $combinationId): Combination
    {
        $request = new StatusRequest();
        $request->combinationId = $combinationId;

        try {
            return $this->combinationApiClient->sendRequest($request)->wait(); // @phpstan-ignore-line
        } catch (ClientException $e) {
            throw new InternalException('Failed to fetch combination details.', $e);
        }
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
                'combination' => $data->combination->id,
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
        if ($exportJob->status === $status) {
            // We do not have to change the status at all.
            return $exportJob;
        }

        $request = new UpdateRequest();
        $request->id = $exportJob->id;
        $request->status = $status;
        $request->errorMessage = $errorMessage;

        return $this->combinationApiClient->sendRequest($request)->wait(); // @phpstan-ignore-line
    }
}
