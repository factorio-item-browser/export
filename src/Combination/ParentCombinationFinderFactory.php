<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Combination;

use FactorioItemBrowser\Export\ExportData\RawExportDataService;
use FactorioItemBrowser\Export\ExportData\ReducedExportDataService;
use FactorioItemBrowser\Export\Merger\MergerManager;
use Interop\Container\ContainerInterface;

/**
 * The factory of the parent combination finder.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ParentCombinationFinderFactory
{
    /**
     * Creates the parent combination finder.
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return ParentCombinationFinder
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var MergerManager $mergerManager */
        $mergerManager = $container->get(MergerManager::class);
        /* @var RawExportDataService $rawExportDataService */
        $rawExportDataService = $container->get(RawExportDataService::class);
        /* @var ReducedExportDataService $reducedExportDataService */
        $reducedExportDataService = $container->get(ReducedExportDataService::class);

        return new ParentCombinationFinder(
            $reducedExportDataService->getCombinationRegistry(),
            $mergerManager,
            $rawExportDataService->getModRegistry()
        );
    }
}
