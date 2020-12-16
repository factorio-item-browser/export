<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Mod;

use BluePsyduck\FactorioModPortalClient\Client\Facade;
use BluePsyduck\FactorioModPortalClient\Entity\Mod;
use BluePsyduck\FactorioModPortalClient\Entity\Release;
use BluePsyduck\FactorioModPortalClient\Entity\Version;
use BluePsyduck\FactorioModPortalClient\Exception\ClientException;
use BluePsyduck\FactorioModPortalClient\Request\ModListRequest;
use BluePsyduck\FactorioModPortalClient\Utils\ModUtils;
use BluePsyduck\SymfonyProcessManager\ProcessManager;
use BluePsyduck\SymfonyProcessManager\ProcessManagerInterface;
use FactorioItemBrowser\Common\Constant\Constant;
use FactorioItemBrowser\Export\Output\Console;
use FactorioItemBrowser\Export\Output\ProgressBar;
use FactorioItemBrowser\Export\Exception\DownloadFailedException;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Exception\InternalException;
use FactorioItemBrowser\Export\Exception\MissingModException;
use FactorioItemBrowser\Export\Exception\NoValidReleaseException;
use FactorioItemBrowser\Export\Output\ModListOutput;
use FactorioItemBrowser\Export\Process\ModDownloadProcess;
use FactorioItemBrowser\Export\Process\ModDownloadProcessFactory;

/**
 * The class responsible for downloading requested mods to the local storage.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ModDownloader
{
    protected Console $console;
    protected ModDownloadProcessFactory $modDownloadProcessFactory;
    protected ModFileManager $modFileManager;
    protected Facade $modPortalClientFacade;
    protected int $numberOfParallelDownloads;

    protected ?Version $factorioVersion = null;
    protected ProgressBar $modsProgressBar;
    protected ModListOutput $modListOutput;

    public function __construct(
        Console $console,
        ModDownloadProcessFactory $modDownloadProcessFactory,
        ModFileManager $modFileManager,
        Facade $modPortalClientFacade,
        int $numberOfParallelDownloads
    ) {
        $this->console = $console;
        $this->modDownloadProcessFactory = $modDownloadProcessFactory;
        $this->modFileManager = $modFileManager;
        $this->modPortalClientFacade = $modPortalClientFacade;
        $this->numberOfParallelDownloads = $numberOfParallelDownloads;
    }

    /**
     * Downloads the specified mods if they are not already available and up-to-date.
     * @param array<string> $modNames
     * @throws ExportException
     */
    public function download(array $modNames): void
    {
        $currentVersions = $this->getCurrentVersions($modNames);
        $mods = $this->fetchMetaData($modNames);
        $this->verifyMods($modNames, $mods);

        $this->modListOutput = $this->console->createModListOutput();
        $this->modsProgressBar = $this->console->createProgressBar('Downloading mods');

        $numberOfMods = 0;
        $processManager = $this->createProcessManager();
        foreach ($mods as $mod) {
            $release = $this->getReleaseToDownload($mod, $currentVersions[$mod->getName()] ?? null);

            $this->modListOutput->add(
                $mod->getName(),
                $currentVersions[$mod->getName()] ?? null,
                $release !== null ? $release->getVersion() : null,
            );

            if ($release !== null) {
                ++$numberOfMods;
                $processManager->addProcess($this->modDownloadProcessFactory->create($mod, $release));
            }
        }
        $this->modListOutput->render();
        if ($numberOfMods > 0) {
            $this->modsProgressBar->setNumberOfSteps($numberOfMods);
        }

        $processManager->waitForAllProcesses();
    }

    /**
     * @param array<string> $modNames
     * @return array<string, ?Version>
     */
    protected function getCurrentVersions(array $modNames): array
    {
        $modVersions = [];
        foreach ($modNames as $modName) {
            try {
                $modVersions[$modName] = $this->modFileManager->getInfo($modName)->version;
            } catch (ExportException $e) {
                $modVersions[$modName] = null;
            }
        }
        return $modVersions;
    }

    /**
     * Fetches the meta data to the specified mod names.
     * @param array<string> $modNames
     * @return array<string, Mod>
     * @throws ExportException
     */
    protected function fetchMetaData(array $modNames): array
    {
        $request = new ModListRequest();
        $request->setNameList($modNames)
                ->setPageSize(count($modNames));

        $result = [];
        try {
            $response = $this->modPortalClientFacade->getModList($request);
            foreach ($response->getResults() as $mod) {
                $result[$mod->getName()] = $mod;
            }
        } catch (ClientException $e) {
            throw new InternalException('Unable to load mods from mod portal: ' . $e->getMessage(), $e);
        }
        return $result;
    }

    /**
     * Verifies that all mods are present which have been requested.
     * @param array<string> $modNames
     * @param array<string, Mod> $mods
     * @throws ExportException
     */
    protected function verifyMods(array $modNames, array $mods): void
    {
        foreach ($modNames as $modName) {
            if ($modName === Constant::MOD_NAME_BASE) {
                continue;
            }

            if (!isset($mods[$modName])) {
                throw new MissingModException($modName);
            }
        }
    }

    /**
     * Returns the release to actually download, or null if no download is required.
     * @param Mod $mod
     * @param Version|null $currentVersion
     * @return Release|null
     * @throws ExportException
     */
    protected function getReleaseToDownload(Mod $mod, ?Version $currentVersion): ?Release
    {
        $release = $this->findLatestRelease($mod);
        if ($currentVersion === null || $release->getVersion()->compareTo($currentVersion) > 0) {
            return $release;
        }

        return null;
    }

    /**
     * Returns the latest release of the mod.
     * @param Mod $mod
     * @return Release
     * @throws ExportException
     */
    protected function findLatestRelease(Mod $mod): Release
    {
        $result = ModUtils::selectLatestRelease($mod, $this->getFactorioVersion());
        if ($result === null) {
            throw new NoValidReleaseException($mod->getName());
        }
        return $result;
    }

    /**
     * Returns the current version of Factorio.
     * @return Version
     * @throws ExportException
     */
    protected function getFactorioVersion(): Version
    {
        if ($this->factorioVersion === null) {
            $this->factorioVersion = $this->modFileManager->getInfo(Constant::MOD_NAME_BASE)->version;
        }
        return $this->factorioVersion;
    }

    /**
     * Creates the process manager to use for the download processes.
     * @return ProcessManagerInterface
     */
    protected function createProcessManager(): ProcessManagerInterface
    {
        $result = new ProcessManager($this->numberOfParallelDownloads);
        $result->setProcessStartCallback(function (ModDownloadProcess $process): void {
            $this->handleProcessStart($process);
        });
        $result->setProcessFinishCallback(function (ModDownloadProcess $process): void {
            $this->handleProcessFinish($process);
        });
        return $result;
    }

    /**
     * Handles the start of a download process.
     * @param ModDownloadProcess<string> $process
     */
    protected function handleProcessStart(ModDownloadProcess $process): void
    {
        $modName = $process->getMod()->getName();
        $this->modsProgressBar->start($modName, "<fg=yellow>Downloading</> {$modName}");
    }

    /**
     * Handles a download process which just finished.
     * @param ModDownloadProcess<string> $process
     * @throws ExportException
     */
    protected function handleProcessFinish(ModDownloadProcess $process): void
    {
        if (!$process->isSuccessful()) {
            throw new DownloadFailedException($process->getMod(), $process->getRelease(), 'Command failed.');
        }

        if (sha1_file($process->getDestinationFile()) !== $process->getRelease()->getSha1()) {
            unlink($process->getDestinationFile());
            throw new DownloadFailedException($process->getMod(), $process->getRelease(), 'Hash mismatch.');
        }

        $modName = $process->getMod()->getName();

        $this->modsProgressBar->update($modName, "<fg=blue>Extracting</> {$modName}");
        $this->modFileManager->extractModZip($modName, $process->getDestinationFile());
        unlink($process->getDestinationFile());
        $this->modsProgressBar->finish($modName);
    }
}
