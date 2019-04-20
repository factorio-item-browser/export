<?php

namespace FactorioItemBrowser\Export\Command\Reduce;

use FactorioItemBrowser\Export\ExportData\RawExportDataService;
use FactorioItemBrowser\Export\ExportData\ReducedExportDataService;
use FactorioItemBrowser\Export\Reducer\Mod\ModReducerManager;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * The factory of the reduce mod command.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ReduceModCommandFactory implements FactoryInterface
{
    /**
     * Creates the command.
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return ReduceModCommand
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var ModReducerManager $modReducerManager */
        $modReducerManager = $container->get(ModReducerManager::class);
        /* @var RawExportDataService $rawExportDataService */
        $rawExportDataService = $container->get(RawExportDataService::class);
        /* @var ReducedExportDataService $reducedExportDataService */
        $reducedExportDataService = $container->get(ReducedExportDataService::class);

        return new ReduceModCommand(
            $modReducerManager,
            $rawExportDataService->getModRegistry(),
            $reducedExportDataService->getModRegistry()
        );
    }
}
