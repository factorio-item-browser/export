<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command;

use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Factorio\FactorioManager;
use FactorioItemBrowser\Export\Mod\CombinationCreator;
use FactorioItemBrowser\ExportData\Entity\Mod;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;
use FactorioItemBrowser\ExportData\Service\ExportDataService;
use Zend\Console\Adapter\AdapterInterface;
use Zend\Console\ColorInterface;
use ZF\Console\Route;

/**
 * The command for export a certain mod.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ExportModCommand implements CommandInterface
{
    /**
     * The export data service.
     * @var ExportDataService
     */
    protected $exportDataService;

    /**
     * The combination creator.
     * @var CombinationCreator
     */
    protected $combinationCreator;

    /**
     * The factorio manager.
     * @var FactorioManager
     */
    protected $factorioManager;

    /**
     * Initializes the command.
     * @param ExportDataService $exportDataService
     * @param CombinationCreator $combinationCreator
     * @param FactorioManager $factorioManager
     */
    public function __construct(
        ExportDataService $exportDataService,
        CombinationCreator $combinationCreator,
        FactorioManager $factorioManager
    )
    {
        $this->exportDataService = $exportDataService;
        $this->combinationCreator = $combinationCreator;
        $this->factorioManager = $factorioManager;
    }

    /**
     * Invokes the command.
     * @param Route $route
     * @param AdapterInterface $console
     */
    public function __invoke(Route $route, AdapterInterface $console)
    {
        $modName = $route->getMatchedParam('modName');

        $console->writeLine(str_pad('', $console->getWidth(), '-'), ColorInterface::YELLOW);
        $console->writeLine(' Exporting mod: ' . $modName, ColorInterface::YELLOW);
        $console->writeLine(str_pad('', $console->getWidth(), '-'), ColorInterface::YELLOW);

        $mod = $this->exportDataService->getMod($modName);
        if (!$mod instanceof Mod) {
            throw new ExportException('Mod not known: ' . $modName);
        }

        $combinations = $this->combinationCreator->createCombinations($mod);
        foreach ($combinations as $combination) {
            $this->factorioManager->addCombination($combination);
        }
        $this->factorioManager->waitForAllCombinations();

        foreach ($combinations as $combination) {
            if (!$this->isCombinationEmpty($combination)) {
                $this->exportDataService->saveCombinationData($combination);
                $mod->addCombination($combination);
            }
        }
        // @todo Translate Mod Meta
        // @todo Render icons
        $this->exportDataService->saveMods();
    }

    /**
     * Returns whether the specified combination is empty.
     * @param Combination $combination
     * @return bool
     */
    protected function isCombinationEmpty(Combination $combination): bool
    {
        return count($combination->getData()->getItems()) === 0
            && count($combination->getData()->getRecipes()) === 0
            && count($combination->getData()->getIcons()) === 0;
    }
}