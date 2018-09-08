<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Mod;

use FactorioItemBrowser\Export\ExportData\RawExportDataService;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * The factory of the dependency resolver.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class DependencyResolverFactory implements FactoryInterface
{
    /**
     * Creates the dependency resolver.
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return DependencyResolver
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var RawExportDataService $rawExportDataService */
        $rawExportDataService = $container->get(RawExportDataService::class);

        return new DependencyResolver($rawExportDataService->getModRegistry());
    }
}
