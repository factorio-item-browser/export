<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Reducer;

use FactorioItemBrowser\Export\Combination\ParentCombinationFinder;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * The factory of the reducer manager.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ReducerManagerFactory implements FactoryInterface
{
    /**
     * The reducer classes to use.
     */
    const REDUCER_CLASSES = [
        IconReducer::class,
        ItemReducer::class,
        MachineReducer::class,
        RecipeReducer::class,
    ];

    /**
     * Creates the reducer manager.
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return ReducerManager
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var ParentCombinationFinder $parentCombinationFinder */
        $parentCombinationFinder = $container->get(ParentCombinationFinder::class);

        $reducers = [];
        foreach (self::REDUCER_CLASSES as $reducerClass) {
            $reducers[] = $container->get($reducerClass);
        }

        return new ReducerManager($parentCombinationFinder, $reducers);
    }
}
