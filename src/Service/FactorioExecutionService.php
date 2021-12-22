<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Service;

use BluePsyduck\FactorioModPortalClient\Entity\Dependency;
use BluePsyduck\FactorioModPortalClient\Entity\Version;
use BluePsyduck\LaminasAutoWireFactory\Attribute\ReadConfig;
use FactorioItemBrowser\Common\Constant\Constant;
use FactorioItemBrowser\Export\AutoWire\Attribute\ReadDirectoryFromConfig;
use FactorioItemBrowser\Export\Constant\ConfigKey;
use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\Entity\InfoJson;
use FactorioItemBrowser\Export\Entity\ModList\Mod;
use FactorioItemBrowser\Export\Entity\ModListJson;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Process\FactorioProcessFactory;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * The service for executing Factorio with a certain set of mods.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class FactorioExecutionService
{
    public function __construct(
        protected readonly SerializerInterface $exportSerializer,
        protected readonly FactorioProcessFactory $factorioProcessFactory,
        protected readonly Filesystem $fileSystem,
        protected readonly ModFileService $modFileService,
        #[ReadDirectoryFromConfig(ConfigKey::DIRECTORY_FACTORIO_HEADLESS)]
        protected readonly string $headlessFactorioDirectory,
        #[ReadDirectoryFromConfig(ConfigKey::DIRECTORY_INSTANCES)]
        protected readonly string $instancesDirectory,
        #[ReadDirectoryFromConfig(ConfigKey::DIRECTORY_LOGS)]
        protected readonly string $logsDirectory,
        #[ReadConfig('version')]
        protected readonly string $version,
    ) {
    }

    /**
     * Prepares the instance for the specified combination id, using the specified mods.
     * @param string $combinationId
     * @param array<string> $modNames
     * @return $this
     * @throws ExportException
     */
    public function prepare(string $combinationId, array $modNames): self
    {
        $this->setupInstanceDirectory($combinationId);
        $this->setupMods($combinationId, $modNames);
        $this->setupDumpMod($combinationId, $modNames);

        return $this;
    }

    protected function setupInstanceDirectory(string $combinationId): void
    {
        $instanceDirectory = "{$this->instancesDirectory}/{$combinationId}";

        $this->fileSystem->remove($instanceDirectory);
        $this->fileSystem->mkdir("{$instanceDirectory}/mods");
        $this->fileSystem->copy(
            "{$this->headlessFactorioDirectory}/bin/x64/factorio",
            "{$instanceDirectory}/bin/x64/factorio",
            true,
        );
        $this->fileSystem->copy(
            "{$this->headlessFactorioDirectory}/config-path.cfg",
            "{$instanceDirectory}/config-path.cfg",
            true,
        );
        $this->fileSystem->symlink("{$this->headlessFactorioDirectory}/data", "{$instanceDirectory}/data");
    }

    /**
     * @param string $combinationId
     * @param array<string> $modNames
     */
    protected function setupMods(string $combinationId, array $modNames): void
    {
        foreach ($modNames as $modName) {
            if ($modName !== Constant::MOD_NAME_BASE) {
                $this->fileSystem->symlink(
                    $this->modFileService->getLocalDirectory($modName),
                    "{$this->instancesDirectory}/{$combinationId}/mods/{$modName}",
                );
            }
        }

        $this->fileSystem->dumpFile(
            "{$this->instancesDirectory}/{$combinationId}/mods/mod-list.json",
            $this->exportSerializer->serialize($this->createModListJson($modNames), 'json'),
        );
    }

    /**
     * @param string $combinationId
     * @param array<string> $modNames
     * @throws ExportException
     */
    protected function setupDumpMod(string $combinationId, array $modNames): void
    {
        $this->fileSystem->mirror(
            __DIR__ . '/../../lua/dump',
            "{$this->instancesDirectory}/{$combinationId}/mods/Dump",
        );
        $this->fileSystem->dumpFile(
            "{$this->instancesDirectory}/{$combinationId}/mods/Dump/info.json",
            $this->exportSerializer->serialize($this->createDumpInfoJson($modNames), 'json'),
        );
    }

    /**
     * @param array<string> $modNames
     * @return InfoJson
     * @throws ExportException
     */
    protected function createDumpInfoJson(array $modNames): InfoJson
    {
        $baseInfo = $this->modFileService->getInfo(Constant::MOD_NAME_BASE);

        $info = new InfoJson();
        $info->name = 'Dump';
        $info->title = 'Factorio Item Browser - Dump';
        $info->author = 'factorio-item-browser';
        $info->version = new Version($this->version);
        $info->factorioVersion = $baseInfo->version;
        foreach ($modNames as $modName) {
            $info->dependencies[] = new Dependency($modName);
        }
        return $info;
    }

    /**
     * @param array<string> $modNames
     * @return ModListJson
     */
    protected function createModListJson(array $modNames): ModListJson
    {
        $modList = new ModListJson();

        // Base mod must always be present, especially if disabled.
        $baseMod = new Mod();
        $baseMod->name = Constant::MOD_NAME_BASE;
        $baseMod->isEnabled = (in_array(Constant::MOD_NAME_BASE, $modNames, true));
        $modList->mods[] = $baseMod;

        // Dump mod must always be enabled.
        $dumpMod = new Mod();
        $dumpMod->name = 'Dump';
        $dumpMod->isEnabled = true;
        $modList->mods[] = $dumpMod;

        // Add all the other mods as well.
        foreach ($modNames as $modName) {
            if ($modName === Constant::MOD_NAME_BASE) {
                continue;
            }

            $mod = new Mod();
            $mod->name = $modName;
            $mod->isEnabled = true;
            $modList->mods[] = $mod;
        }

        return $modList;
    }

    /**
     * Executes the already prepared instance for the combination.
     * @param string $combinationId
     * @return Dump
     * @throws ExportException
     */
    public function execute(string $combinationId): Dump
    {
        $process = $this->factorioProcessFactory->create("{$this->instancesDirectory}/{$combinationId}");
        $process->run();
        return $process->getDump();
    }

    /**
     * Cleans up the instance of the specified combination.
     * @param string $combinationId
     * @return $this
     */
    public function cleanup(string $combinationId): self
    {
        $this->fileSystem->copy(
            "{$this->instancesDirectory}/{$combinationId}/factorio-current.log",
            "{$this->logsDirectory}/factorio_{$combinationId}.log",
        );
        $this->fileSystem->remove("{$this->instancesDirectory}/{$combinationId}");
        return $this;
    }
}
