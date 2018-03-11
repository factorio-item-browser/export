<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Factorio;

use FactorioItemBrowser\Export\Reducer\ReducerManager;
use FactorioItemBrowser\ExportData\Service\ExportDataService;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\ServiceManager\ServiceManager;

/**
 * The factory of the Factorio manager.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class FactorioManagerFactory implements FactoryInterface
{
    /**
     * Creates the Factorio manager.
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return FactorioManager
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var ServiceManager $container */
        $config = $container->get('config');

        /* @var ExportDataService $exportDataService */
        $exportDataService = $container->get(ExportDataService::class);
        /* @var ReducerManager $reducerManager */
        $reducerManager = $container->get(ReducerManager::class);

        $instances = [];
        for ($index = 0; $index < $config['factorio']['numberOfInstances']; ++$index) {
            $instances[] = $container->build(Instance::class);
        }

        return new FactorioManager(
            $exportDataService,
            $reducerManager,
            $config['factorio']['modsDirectory'],
            $instances
        );
    }
}