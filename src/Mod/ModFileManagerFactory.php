<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Mod;

use FactorioItemBrowser\Export\I18n\LocaleFileReader;
use FactorioItemBrowser\ExportData\Service\ExportDataService;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * The factory of the mod file manager.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ModFileManagerFactory implements FactoryInterface
{
    /**
     * Creates the mod file manager.
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return ModFileManager
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');
        /* @var ExportDataService $exportDataService */
        $exportDataService = $container->get(ExportDataService::class);
        /* @var LocaleFileReader $localeFileReader */
        $localeFileReader = $container->get(LocaleFileReader::class);

        return new ModFileManager($config['factorio']['directory'] . '/mods', $exportDataService, $localeFileReader);
    }
}