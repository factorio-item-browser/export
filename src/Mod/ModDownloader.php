<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Mod;

use BluePsyduck\FactorioModPortalClient\Client\Facade;
use BluePsyduck\FactorioModPortalClient\Entity\Mod;
use BluePsyduck\FactorioModPortalClient\Entity\Release;
use BluePsyduck\FactorioModPortalClient\Exception\ClientException;
use BluePsyduck\FactorioModPortalClient\Request\ModListRequest;
use BluePsyduck\SymfonyProcessManager\ProcessManager;
use BluePsyduck\SymfonyProcessManager\ProcessManagerInterface;
use FactorioItemBrowser\Common\Constant\Constant;
use FactorioItemBrowser\Export\Console\Console;
use FactorioItemBrowser\Export\Exception\DownloadFailedException;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Exception\InternalException;
use FactorioItemBrowser\Export\Exception\MissingModException;
use FactorioItemBrowser\Export\Exception\NoValidReleaseException;
use FactorioItemBrowser\Export\Process\DownloadProcess;
use FactorioItemBrowser\Export\Utils\VersionUtils;
use Symfony\Component\Process\Process;

/**
 * The class responsible for downloading requested mods to the local storage.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ModDownloader
{
    /**
     * The console.
     * @var Console
     */
    protected $console;

    /**
     * The mod file manager.
     * @var ModFileManager
     */
    protected $modFileManager;

    /**
     * The mod portal client facade.
     * @var Facade
     */
    protected $modPortalClientFacade;

    /**
     * The number of parallel downloads to use.
     * @var int
     */
    protected $numberOfParallelDownloads;

    /**
     * The temp directory to store downloaded mods in.
     * @var string
     */
    protected $tempDirectory;

    /**
     * Initializes the downloader.
     * @param Console $console
     * @param ModFileManager $modFileManager
     * @param Facade $modPortalClientFacade
     * @param int $numberOfParallelDownloads
     * @param string $tempDirectory
     */
    public function __construct(
        Console $console,
        ModFileManager $modFileManager,
        Facade $modPortalClientFacade,
        int $numberOfParallelDownloads,
        string $tempDirectory
    ) {
        $this->console = $console;
        $this->modFileManager = $modFileManager;
        $this->modPortalClientFacade = $modPortalClientFacade;
        $this->numberOfParallelDownloads = $numberOfParallelDownloads;
        $this->tempDirectory = $tempDirectory;
    }

    /**
     * Downloads the specified mods if they are not already available and up-to-date.
     * @param array|string[] $modNames
     * @throws ExportException
     */
    public function download(array $modNames): void
    {
        $this->console->writeAction('Loading meta information from the Mod Portal');
        $mods = $this->fetchMetaData($modNames);
        $this->verifyMods($modNames, $mods);

        $processManager = $this->createProcessManager();
        foreach ($mods as $mod) {
            $release = $this->getReleaseToDownload($mod);
            if ($release === null) {
                $this->console->writeMessage('Mod %s is already up-to-date.', $mod->getName());
                continue;
            }
            $processManager->addProcess($this->createDownloadProcess($mod, $release));
        }
        $processManager->waitForAllProcesses();
    }

    /**
     * Fetches the meta data to the specified mod names.
     * @param array|string[] $modNames
     * @return array|Mod[]
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
     * @param array|string[] $modNames
     * @param array|Mod[] $mods
     * @throws ExportException
     */
    protected function verifyMods(array $modNames, array $mods): void
    {
        $hasBase = false;
        foreach ($modNames as $modName) {
            if ($modName === Constant::MOD_NAME_BASE) {
                $hasBase = true;
            } elseif (!isset($mods[$modName])) {
                throw new MissingModException($modName);
            }
        }

        if (!$hasBase) {
            throw new MissingModException(Constant::MOD_NAME_BASE);
        }
    }

    /**
     * Returns the release to actually download, or null if no download is required.
     * @param Mod $mod
     * @return Release|null
     * @throws ExportException
     */
    protected function getReleaseToDownload(Mod $mod): ?Release
    {
        $result = null;
        try {
            $currentVersion = $this->modFileManager->getInfo($mod->getName())->getVersion();
        } catch (ExportException $e) {
            $currentVersion = '';
        }

        $release = $this->findLatestRelease($mod);
        if ($currentVersion === '' || VersionUtils::compare($release->getVersion(), $currentVersion) > 0) {
            $result = $release;
        }
        return $result;
    }

    /**
     * Returns the latest release of the mod.
     * @param Mod $mod
     * @return Release
     * @throws ExportException
     */
    protected function findLatestRelease(Mod $mod): Release
    {
        if ($mod->getLatestRelease() instanceof Release) {
            return $mod->getLatestRelease();
        }

        /* @var Release|null $result */
        $result = null;
        foreach ($mod->getReleases() as $release) {
            if ($result === null || VersionUtils::compare($release->getVersion(), $result->getVersion()) > 0) {
                $result = $release;
            }
        }
        if ($result === null) {
            throw new NoValidReleaseException($mod->getName());
        }
        return $result;
    }

    /**
     * Creates the process manager to use for the download processes.
     * @return ProcessManagerInterface
     */
    protected function createProcessManager(): ProcessManagerInterface
    {
        $result = new ProcessManager($this->numberOfParallelDownloads);
        $result->setProcessStartCallback(function (DownloadProcess $process): void {
            $this->handleProcessStart($process);
        });
        $result->setProcessFinishCallback(function (DownloadProcess $process): void {
            $this->handleProcessFinish($process);
        });
        return $result;
    }

    /**
     * Creates a download process for the specified mod and release.
     * @param Mod $mod
     * @param Release $release
     * @return Process
     */
    protected function createDownloadProcess(Mod $mod, Release $release): Process
    {
        return new DownloadProcess(
            $mod,
            $release,
            $this->modPortalClientFacade->getDownloadUrl($release->getDownloadUrl()),
            $this->tempDirectory . '/' . $release->getFileName()
        );
    }

    /**
     * Handles the start of a download process.
     * @param DownloadProcess $process
     */
    protected function handleProcessStart(DownloadProcess $process): void
    {
        $this->console->writeAction(
            'Downloading %s (%s)',
            $process->getMod()->getName(),
            $process->getRelease()->getVersion()
        );
    }

    /**
     * Handles a download process which just finished.
     * @param DownloadProcess $process
     * @throws ExportException
     */
    protected function handleProcessFinish(DownloadProcess $process): void
    {
        if (!$process->isSuccessful()) {
            throw new DownloadFailedException($process->getMod(), $process->getRelease(), 'Command failed.');
        }

        if (sha1_file($process->getDestinationFile()) !== $process->getRelease()->getSha1()) {
            unlink($process->getDestinationFile());
            throw new DownloadFailedException($process->getMod(), $process->getRelease(), 'Hash mismatch.');
        }

        $this->console->writeAction('Extracting %s', $process->getMod()->getName());
        $this->modFileManager->extractModZip($process->getDestinationFile());
        unlink($process->getDestinationFile());
    }
}
