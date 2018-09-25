<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Combination;

use FactorioItemBrowser\Export\ExportData\RawExportDataService;
use FactorioItemBrowser\Export\ExportData\ReducedExportDataService;
use FactorioItemBrowser\Export\Mod\DependencyResolver;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * The factory of the combination creator.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class CombinationCreatorFactory implements FactoryInterface
{
    /**
     * Creates the combination creator.
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return CombinationCreator
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var DependencyResolver $dependencyResolver */
        $dependencyResolver = $container->get(DependencyResolver::class);
        /* @var RawExportDataService $rawExportDataService */
        $rawExportDataService = $container->get(RawExportDataService::class);
        /* @var ReducedExportDataService $reducedExportDataService */
        $reducedExportDataService = $container->get(ReducedExportDataService::class);

        return new CombinationCreator(
            $reducedExportDataService->getCombinationRegistry(),
            $dependencyResolver,
            $rawExportDataService->getModRegistry()
        );
    }
}
