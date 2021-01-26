<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Service;

use BluePsyduck\FactorioModPortalClient\Client\Facade;
use BluePsyduck\FactorioModPortalClient\Entity\Mod;
use BluePsyduck\FactorioModPortalClient\Entity\Release;
use BluePsyduck\FactorioModPortalClient\Entity\Version;
use BluePsyduck\FactorioModPortalClient\Exception\ClientException;
use BluePsyduck\FactorioModPortalClient\Request\ModListRequest;
use BluePsyduck\FactorioModPortalClient\Utils\ModUtils;
use FactorioItemBrowser\Common\Constant\Constant;
use FactorioItemBrowser\Export\Output\Console;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Exception\InternalException;
use FactorioItemBrowser\Export\Exception\MissingModsException;
use FactorioItemBrowser\Export\Exception\NoValidReleaseException;
use FactorioItemBrowser\Export\Process\ModDownloadProcessManager;

/**
 * The class responsible for downloading requested mods to the local storage.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ModDownloadService
{
    protected Console $console;
    protected ModDownloadProcessManager $modDownloadProcessManager;
    protected ModFileService $modFileService;
    protected Facade $modPortalClientFacade;

    protected ?Version $factorioVersion = null;

    /**
     * @param Console $console
     * @param ModDownloadProcessManager $modDownloadProcessManager
     * @param ModFileService $modFileService
     * @param Facade $modPortalClientFacade
     * @throws ExportException
     */
    public function __construct(
        Console $console,
        ModDownloadProcessManager $modDownloadProcessManager,
        ModFileService $modFileService,
        Facade $modPortalClientFacade
    ) {
        $this->console = $console;
        $this->modDownloadProcessManager = $modDownloadProcessManager;
        $this->modFileService = $modFileService;
        $this->modPortalClientFacade = $modPortalClientFacade;

        $this->factorioVersion = $this->modFileService->getInfo(Constant::MOD_NAME_BASE)->version;
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
        $releases = $this->getReleases($mods, $currentVersions);

        $this->printModList($mods, $currentVersions, $releases);

        $releases = array_filter($releases);
        foreach ($releases as $modName => $release) {
            $this->modDownloadProcessManager->add($mods[$modName], $release);
        }
        $this->modDownloadProcessManager->wait();
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
                $modVersions[$modName] = $this->modFileService->getInfo($modName)->version;
            } catch (ExportException $e) {
                $modVersions[$modName] = null;
            }
        }
        return $modVersions;
    }

    /**
     * @param array<string> $modNames
     * @return array<string, Mod>
     * @throws ExportException
     */
    protected function fetchMetaData(array $modNames): array
    {
        $missingMods = array_flip($modNames);

        $request = new ModListRequest();
        $request->setNameList($modNames)
                ->setPageSize(count($modNames));

        $result = [];
        try {
            $response = $this->modPortalClientFacade->getModList($request);
            foreach ($response->getResults() as $mod) {
                $result[$mod->getName()] = $mod;
                unset($missingMods[$mod->getName()]);
            }
        } catch (ClientException $e) {
            throw new InternalException('Unable to load mods from mod portal: ' . $e->getMessage(), $e);
        }

        unset($missingMods[Constant::MOD_NAME_BASE]);
        if (count($missingMods) > 0) {
            throw new MissingModsException($missingMods);
        }

        return $result;
    }

    /**
     * @param array<string, Mod> $mods
     * @param array<string, ?Version> $currentVersions
     * @return array<?Release>
     * @throws ExportException
     */
    protected function getReleases(array $mods, array $currentVersions): array
    {
        $releases = [];
        foreach ($mods as $mod) {
            $release = ModUtils::selectLatestRelease($mod, $this->factorioVersion);
            if ($release === null) {
                throw new NoValidReleaseException($mod->getName());
            }

            $currentVersion = $currentVersions[$mod->getName()] ?? null;
            if ($currentVersion === null || $release->getVersion()->compareTo($currentVersion) > 0) {
                $releases[$mod->getName()] = $release;
            } else {
                $releases[$mod->getName()] = null;
            }
        }
        return $releases;
    }

    /**
     * @param array<string, Mod> $mods
     * @param array<string, ?Version> $currentVersions
     * @param array<string, ?Release> $releases
     */
    protected function printModList(array $mods, array $currentVersions, array $releases): void
    {
        $modListOutput = $this->console->createModListOutput();
        foreach ($mods as $mod) {
            $release = $releases[$mod->getName()] ?? null;
            $modListOutput->add(
                $mod->getName(),
                $currentVersions[$mod->getName()] ?? null,
                $release !== null ? $release->getVersion() : null,
            );
        }
        $modListOutput->render();
    }
}
