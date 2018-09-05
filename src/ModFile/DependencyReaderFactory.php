<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\ModFile;

use Interop\Container\ContainerInterface;

/**
 * The factory of the dependency reader.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class DependencyReaderFactory
{
    /**
     * Creates the dependency reader.
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return DependencyReader
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var ModFileManager $modFileManager */
        $modFileManager = $container->get(ModFileManager::class);

        return new DependencyReader($modFileManager);
    }
}
