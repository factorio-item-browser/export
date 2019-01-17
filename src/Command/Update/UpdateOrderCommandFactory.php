<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command\Update;

use FactorioItemBrowser\Export\ExportData\RawExportDataService;
use FactorioItemBrowser\Export\Mod\DependencyResolver;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * The factory of the update order command.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class UpdateOrderCommandFactory implements FactoryInterface
{
    /**
     * Creates the command.
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return UpdateOrderCommand
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var DependencyResolver $dependencyResolver */
        $dependencyResolver = $container->get(DependencyResolver::class);
        /* @var RawExportDataService $exportDataService */
        $exportDataService = $container->get(RawExportDataService::class);

        return new UpdateOrderCommand(
            $dependencyResolver,
            $exportDataService->getModRegistry()
        );
    }
}
