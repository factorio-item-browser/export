<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Combination;

use FactorioItemBrowser\Export\Mod\DependencyResolver;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * The factory of the combination creator.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class CombinationCreatorFactory implements FactoryInterface
{
    /**
     * Creates the combination creator.
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return CombinationCreator
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var DependencyResolver $dependencyResolver */
        $dependencyResolver = $container->get(DependencyResolver::class);

        return new CombinationCreator($dependencyResolver);
    }
}
