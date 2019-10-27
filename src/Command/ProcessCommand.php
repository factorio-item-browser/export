<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command;

use Exception;
use FactorioItemBrowser\Export\Console\Console;
use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Exception\InternalException;
use FactorioItemBrowser\Export\Factorio\Instance;
use FactorioItemBrowser\Export\Mod\ModDownloader;
use FactorioItemBrowser\Export\Parser\ParserManager;
use FactorioItemBrowser\Export\Renderer\IconRenderer;
use FactorioItemBrowser\ExportData\ExportData;
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
     * The icon renderer.
     * @var IconRenderer
     */
    protected $iconRenderer;

    /**
     * The instance.
     * @var Instance
     */
    protected $instance;

    /**
     * The mod downloader.
     * @var ModDownloader
     */
    protected $modDownloader;

    /**
     * The parser manager.
     * @var ParserManager
     */
    protected $parserManager;

    /**
     * The console.
     * @var Console
     */
    protected $console;

    /**
     * ProcessCommand constructor.
     * @param ExportDataService $exportDataService
     * @param Facade $exportQueueFacade
     * @param IconRenderer $iconRenderer
     * @param Instance $instance
     * @param ModDownloader $modDownloader
     * @param ParserManager $parserManager
     */
    public function __construct(
        ExportDataService $exportDataService,
        Facade $exportQueueFacade,
        IconRenderer $iconRenderer,
        Instance $instance,
        ModDownloader $modDownloader,
        ParserManager $parserManager
    ) {
        $this->exportDataService = $exportDataService;
        $this->exportQueueFacade = $exportQueueFacade;
        $this->iconRenderer = $iconRenderer;
        $this->instance = $instance;
        $this->modDownloader = $modDownloader;
        $this->parserManager = $parserManager;
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
     */
    protected function execute(): void
    {
        $exportJob = $this->fetchExportJob();
        if ($exportJob === null) {
            $this->console->writeMessage('No export job to process. Done.');
            return;
        }

        $this->console->writeHeadline('Processing combination %s', $exportJob->getCombinationId());
        $export = $this->exportDataService->createExport($exportJob->getCombinationId());

        $exportJob = $this->updateExportJob($exportJob, JobStatus::DOWNLOADING);
        $this->downloadMods($exportJob->getModNames());
        $exportJob = $this->updateExportJob($exportJob, JobStatus::PROCESSING);
        $dump = $this->runFactorio($export, $exportJob->getModNames());
        $this->parseDump($export, $dump);
        $this->renderIcons($export);

        $exportJob = $this->updateExportJob($exportJob, JobStatus::UPLOADING);
        $fileName = $export->persist();
        echo 'Exported combination to: ' . $fileName . PHP_EOL;
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

    protected function updateExportJob(Job $exportJob, string $status, string $errorMessage = ''): Job
    {
        $request = new UpdateRequest();
        $request->setJobId($exportJob->getId())
                ->setStatus($status)
                ->setErrorMessage($errorMessage);

        return $this->exportQueueFacade->updateJob($request);
    }

    /**
     * Downloads all the mods if they are not already present in their latest version.
     * @param array|string[] $modNames
     * @throws ExportException
     */
    protected function downloadMods(array $modNames): void
    {
        $this->console->writeStep('Downloading %d mods', count($modNames));
        $this->modDownloader->download($modNames);
    }

    /**
     * Runs the Factorio game to dump all the data.
     * @param ExportData $export
     * @param array|string[] $modNames
     * @return Dump
     * @throws ExportException
     */
    protected function runFactorio(ExportData $export, array $modNames): Dump
    {
        $this->console->writeStep('Running Factorio');
        return $this->instance->run($export->getCombination()->getId(), $modNames);
    }

    /**
     * Parses the dumped data into the export.
     * @param ExportData $export
     * @param Dump $dump
     * @throws ExportException
     */
    protected function parseDump(ExportData $export, Dump $dump): void
    {
        $this->console->writeStep('Parsing dumped data');
        $this->parserManager->parse($dump, $export->getCombination());
    }

    /**
     * Renders all the icons of the export.
     * @param ExportData $export
     * @throws ExportException
     */
    protected function renderIcons(ExportData $export): void
    {
        $this->console->writeStep('Rendering %d icons', count($export->getCombination()->getIcons()));

        foreach ($export->getCombination()->getIcons() as $icon) {
            $this->console->writeAction('Rendering icon %s', $icon->getId());
            $export->addRenderedIcon($icon, $this->iconRenderer->render($icon));
        }
    }
}
