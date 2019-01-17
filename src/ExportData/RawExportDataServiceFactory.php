<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\ExportData;

use FactorioItemBrowser\ExportData\Registry\Adapter\FileSystemAdapter;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * The factory of the raw export data service.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class RawExportDataServiceFactory implements FactoryInterface
{
    /**
     * Creates the raw export data service.
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return RawExportDataService
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');
        $adapter = new FileSystemAdapter($config['export-data']['raw']['directory']);

        return new RawExportDataService($adapter);
    }
}
