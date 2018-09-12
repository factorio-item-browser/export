<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Mod;

use FactorioItemBrowser\Export\ExportData\RawExportDataService;
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
        /* @var RawExportDataService $rawExportDataService */
        $rawExportDataService = $container->get(RawExportDataService::class);

        return new ParentCombinationFinder(
            $rawExportDataService->getCombinationRegistry(),
            $rawExportDataService->getModRegistry()
        );
    }
}
