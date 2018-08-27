<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command\Clean;

use FactorioItemBrowser\Export\Cache\LocaleCache;
use FactorioItemBrowser\Export\Cache\ModFileCache;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * The factory of the clear cache command class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class CleanCacheCommandFactory implements FactoryInterface
{
    /**
     * Creates the command.
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return CleanCacheCommand
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var LocaleCache $localeCache */
        $localeCache = $container->get(LocaleCache::class);
        /* @var ModFileCache $modFileCache */
        $modFileCache = $container->get(ModFileCache::class);

        return new CleanCacheCommand([
            $localeCache,
            $modFileCache
        ]);
    }
}
