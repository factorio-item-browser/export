<?php

namespace FactorioItemBrowser\Export\Factorio;

use FactorioItemBrowser\Export\ExportData\RawExportDataService;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * The factory of the dump info generator.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class DumpInfoGeneratorFactory implements FactoryInterface
{
    /**
     * Creates the generator.
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return DumpInfoGenerator
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');

        /* @var RawExportDataService $rawExportDataService */
        $rawExportDataService = $container->get(RawExportDataService::class);

        return new DumpInfoGenerator(
            $rawExportDataService->getModRegistry(),
            $config['factorio']['directory'] . '/mods'
        );
    }
}
