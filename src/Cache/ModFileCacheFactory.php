<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Cache;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * The factory of the mod file cache.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ModFileCacheFactory implements FactoryInterface
{
    /**
     * Creates the mod file cache.
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return ModFileCache
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');

        return new ModFileCache($config['cache']['mod-file']['directory']);
    }
}
