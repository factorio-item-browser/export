<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\ModFile;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * The factory of the mod file reader.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ModFileReaderFactory implements FactoryInterface
{
    /**
     * Creates the mod file reader.
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return ModFileReader
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var ModFileManager $modFileManager */
        $modFileManager = $container->get(ModFileManager::class);

        return new ModFileReader($modFileManager);
    }
}
