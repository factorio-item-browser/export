<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command;

use BluePsyduck\FactorioModPortalClient\Entity\Version;
use FactorioItemBrowser\CombinationApi\Client\ClientInterface;
use FactorioItemBrowser\CombinationApi\Client\Constant\JobPriority;
use FactorioItemBrowser\CombinationApi\Client\Exception\ClientException;
use FactorioItemBrowser\CombinationApi\Client\Request\Job\CreateRequest;
use FactorioItemBrowser\Common\Constant\Constant;
use FactorioItemBrowser\Common\Constant\Defaults;
use FactorioItemBrowser\Export\Constant\CommandName;
use FactorioItemBrowser\Export\Exception\CommandException;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Output\Console;
use FactorioItemBrowser\Export\Process\CommandProcess;
use FactorioItemBrowser\Export\Service\FactorioDownloadService;
use FactorioItemBrowser\Export\Service\ModFileService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The command for updating Factorio to its latest version.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class UpdateFactorioCommand extends Command
{
    protected ClientInterface $combinationApiClient;
    protected Console $console;
    protected FactorioDownloadService $factorioDownloadService;
    protected ModFileService $modFileService;

    public function __construct(
        ClientInterface $combinationApiClient,
        Console $console,
        FactorioDownloadService $factorioDownloadService,
        ModFileService $modFileService,
    ) {
        parent::__construct();

        $this->combinationApiClient = $combinationApiClient;
        $this->console = $console;
        $this->factorioDownloadService = $factorioDownloadService;
        $this->modFileService = $modFileService;
    }

    protected function configure(): void
    {
        parent::configure();

        $this->setName(CommandName::UPDATE_FACTORIO);
        $this->setDescription('Updates the Factorio game to its latest version.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws ExportException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $currentVersion = $this->modFileService->getInfo(Constant::MOD_NAME_BASE)->version;
        $this->console->writeMessage(sprintf('Current version: <fg=green>%s</>', $currentVersion));

        $latestVersion = $this->factorioDownloadService->getLatestVersion();
        $this->console->writeMessage(sprintf('Latest version:  <fg=green>%s</>', $latestVersion));

        if ((new Version($latestVersion))->compareTo($currentVersion) <= 0) {
            $this->console->writeMessage('Already up-to-date. Done.');
            return 0;
        }

        $this->console->writeAction('Downloading new version of Factorio');
        $process = $this->createDownloadProcess($latestVersion);
        $process->run(function (string $type, string $message) use ($output): void {
            $output->write($message);
        });

        if ($process->getExitCode() !== 0) {
            $this->console->writeMessage('<fg=red>Factorio download failed.</>');
            return 1;
        }

        $this->createExportJob();
        return 0;
    }

    /**
     * @param string $version
     * @return CommandProcess<string>
     */
    protected function createDownloadProcess(string $version): CommandProcess
    {
        return new CommandProcess(CommandName::DOWNLOAD_FACTORIO, [$version]);
    }

    /**
     * @throws CommandException
     */
    protected function createExportJob(): void
    {
        $request = new CreateRequest();
        $request->combinationId = Defaults::COMBINATION_ID;
        $request->priority = JobPriority::ADMIN;

        try {
            $this->combinationApiClient->sendRequest($request);
        } catch (ClientException $e) {
            throw new CommandException('Request to Combination API failed.', 500, $e);
        }
    }
}
