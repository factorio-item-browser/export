<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Cache;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * The factory of the locale cache.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class LocaleCacheFactory implements FactoryInterface
{
    /**
     * Creates the locale cache.
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return LocaleCache
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');

        return new LocaleCache($config['cache']['directory']['locale']);
    }
}