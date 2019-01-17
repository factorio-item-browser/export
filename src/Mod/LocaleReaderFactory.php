<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Mod;

use FactorioItemBrowser\Export\Cache\LocaleCache;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * The factory of the locale reader.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class LocaleReaderFactory implements FactoryInterface
{
    /**
     * Creates the locale reader.
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return LocaleReader
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var LocaleCache $localeCache */
        $localeCache = $container->get(LocaleCache::class);
        /* @var ModFileManager $modFileManager */
        $modFileManager = $container->get(ModFileManager::class);

        return new LocaleReader($localeCache, $modFileManager);
    }
}
