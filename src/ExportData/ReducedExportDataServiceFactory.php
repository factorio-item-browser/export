<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\ExportData;

use FactorioItemBrowser\ExportData\Registry\Adapter\FileSystemAdapter;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * The factory of the reduced export data service.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ReducedExportDataServiceFactory implements FactoryInterface
{
    /**
     * Creates the reduced export data service.
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return ReducedExportDataService
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');
        $adapter = new FileSystemAdapter($config['export-data']['reduced']['directory']);

        return new ReducedExportDataService($adapter);
    }
}
