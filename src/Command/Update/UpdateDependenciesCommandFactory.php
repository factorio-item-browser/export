<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command\Update;

use FactorioItemBrowser\Export\ExportData\RawExportDataService;
use FactorioItemBrowser\Export\Mod\DependencyReader;
use Interop\Container\ContainerInterface;

/**
 * The factory of the update dependencies command.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class UpdateDependenciesCommandFactory
{
    /**
     * Creates the command.
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return UpdateDependenciesCommand
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var DependencyReader $dependencyReader */
        $dependencyReader = $container->get(DependencyReader::class);
        /* @var RawExportDataService $rawExportDataService */
        $rawExportDataService = $container->get(RawExportDataService::class);

        return new UpdateDependenciesCommand(
            $dependencyReader,
            $rawExportDataService->getModRegistry()
        );
    }
}
