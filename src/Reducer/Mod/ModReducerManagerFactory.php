<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Reducer\Mod;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * The factory of the mod reducer manager.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ModReducerManagerFactory implements FactoryInterface
{
    /**
     * The reducer classes to use.
     */
    const REDUCER_CLASSES = [
        CombinationReducer::class,
        ThumbnailReducer::class,
    ];

    /**
     * Creates the reducer manager.
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return ModReducerManager
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $reducers = [];
        foreach (self::REDUCER_CLASSES as $reducerClass) {
            $reducers[] = $container->get($reducerClass);
        }

        return new ModReducerManager($reducers);
    }
}
