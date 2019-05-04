<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Reducer\Mod;

use FactorioItemBrowser\Export\ExportData\RawExportDataService;
use FactorioItemBrowser\Export\ExportData\ReducedExportDataService;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * The factory of the thumbnail reducer.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ThumbnailReducerFactory implements FactoryInterface
{
    /**
     * Creates the reducer.
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return ThumbnailReducer
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ThumbnailReducer
    {
        /* @var RawExportDataService $rawExportDataService */
        $rawExportDataService = $container->get(RawExportDataService::class);
        /* @var ReducedExportDataService $reducedExportDataService */
        $reducedExportDataService = $container->get(ReducedExportDataService::class);

        return new ThumbnailReducer(
            $rawExportDataService->getIconRegistry(),
            $reducedExportDataService->getIconRegistry()
        );
    }
}
