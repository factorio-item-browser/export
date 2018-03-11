<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Factorio;

use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Reducer\ReducerManager;
use FactorioItemBrowser\ExportData\Entity\Mod;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;
use FactorioItemBrowser\ExportData\Service\ExportDataService;

/**
 * The class managing the Factorio game with its instances.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class FactorioManager
{
    /**
     * The delay to sleep while waiting for exports, in microseconds.
     */
    private const SLEEP_DELAY = 100000;

    /**
     * The export data service.
     * @var ExportDataService
     */
    protected $exportDataService;

    /**
     * The reducer manager.
     * @var ReducerManager
     */
    protected $reducerManager;

    /**
     * The instances to execute exports on.
     * @var array|Instance[]
     */
    protected $instances;

    /**
     * The combinations waiting to be executed.
     * @var array|Combination[]
     */
    protected $pendingCombinations = [];

    /**
     * Initializes the Factorio manager.
     * @param ExportDataService $exportDataService
     * @param ReducerManager $reducerManager
     * @param string $modsDirectory
     * @param array|Instance[] $instances
     * @throws ExportException
     */
    public function __construct(
        ExportDataService $exportDataService,
        ReducerManager $reducerManager,
        string $modsDirectory,
        array $instances
    ) {
        $this->exportDataService = $exportDataService;
        $this->reducerManager = $reducerManager;
        $this->instances = $instances;

        $this->prepareDumpInfoJson($modsDirectory);
    }

    /**
     * Prepares the info.json file of the dump mod.
     * @param string $modsDirectory
     * @return $this
     * @throws ExportException
     */
    protected function prepareDumpInfoJson(string $modsDirectory)
    {
        $mods = [];
        foreach ($this->exportDataService->getMods() as $mod) {
            $mods[] = '?' . $mod->getName();
        }
        $baseMod = $this->exportDataService->getMod('base');
        if (!$baseMod instanceof Mod) {
            throw new ExportException('Base mod is missing!');
        }

        $json = [
            'name' => 'Dump',
            'version' => '1.0.0',
            'factorio_version' => $baseMod->getVersion(),
            'title' => 'BluePsyduck\'s Dump',
            'author' => 'BluePsyduck',
            'dependencies' => $mods
        ];

        $fullFileName = $modsDirectory . '/Dump_1.0.0/info.json';
        $result = file_put_contents($fullFileName, json_encode($json));
        if ($result === false) {
            throw new ExportException('Unable to write file: ' . $fullFileName);
        }
        return $this;
    }

    /**
     * Adds a combination to be executed.
     * @param Combination $combination
     * @return $this
     * @throws ExportException
     */
    public function addCombination(Combination $combination)
    {
        $this->pendingCombinations[] = $combination;
        $this->executeNextPendingCombination();
        return $this;
    }

    /**
     * Executes the next pending combination, if there happens to be an idle instance.
     * @return $this
     * @throws ExportException
     */
    protected function executeNextPendingCombination()
    {
        if (!empty($this->pendingCombinations)) {
            foreach ($this->instances as $instance) {
                if (!$instance->hasRunningCombination()) {
                    $combination = array_shift($this->pendingCombinations);
                    $instance->execute($combination);
                    break;
                }
            }
        }
        return $this;
    }

    /**
     * Waits for all combinations to finish.
     * @return $this
     * @throws ExportException
     */
    public function waitForAllCombinations()
    {
        while (count($this->pendingCombinations) > 0) {
            $this->executeNextPendingCombination();
            usleep(self::SLEEP_DELAY);
        }
        while ($this->isAnyInstanceBusy()) {
            usleep(self::SLEEP_DELAY);
        }
        $this->reducerManager->reduceAllCombinations();
        return $this;
    }

    /**
     * Checks whether any of the instances is still busy.
     * @return bool
     * @throws ExportException
     */
    protected function isAnyInstanceBusy(): bool
    {
        $result = false;
        foreach ($this->instances as $instance) {
            if ($instance->hasRunningCombination()) {
                $result = true;
                break;
            }
        }
        return $result;
    }
}