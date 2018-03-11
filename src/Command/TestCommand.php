<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command;

use FactorioItemBrowser\Export\Factorio\FactorioManager;
use FactorioItemBrowser\Export\Reducer\ReducerManager;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;
use FactorioItemBrowser\ExportData\Service\ExportDataService;
use Interop\Container\ContainerInterface;
use Zend\Console\Adapter\AdapterInterface;
use ZF\Console\Route;

/**
 * A command for testing purposes.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class TestCommand implements CommandInterface
{
    /**
     * The container.
     * @var ContainerInterface
     */
    protected $container;

    /**
     * TestCommand constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Invokes the command.
     * @param Route $route
     * @param AdapterInterface $console
     */
    public function __invoke(Route $route, AdapterInterface $console)
    {
        /* @var ExportDataService $exportDataService */
        $exportDataService = $this->container->get(ExportDataService::class);

        $combination = new Combination();
        $combination->setName('bobelectronics')
                    ->setMainModName('bobelectronics')
                    ->setLoadedModNames(['base', 'boblibrary', 'bobores', 'bobplates', 'bobelectronics']);

        /* @var FactorioManager $factorioManager */
        $factorioManager = $this->container->get(FactorioManager::class);
        $factorioManager->addCombination($combination);
        $factorioManager->waitForAllCombinations();

        /* @var ReducerManager $reducerManager */
        $reducerManager = $this->container->get(ReducerManager::class);
        $reducerManager->reduce($combination, $exportDataService->loadCombination('base', 'base'));

        $exportDataService->saveCombination($combination);
    }
}