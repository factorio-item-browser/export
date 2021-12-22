<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\AutoWire\Resolver;

use BluePsyduck\LaminasAutoWireFactory\Resolver\ConfigResolver;
use FactorioItemBrowser\Export\Constant\ConfigKey;
use Psr\Container\ContainerInterface;

/**
 * The resolver for reading a directory path from the config.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ConfigDirectoryResolver extends ConfigResolver
{
    public function __construct(string $key)
    {
        parent::__construct([ConfigKey::MAIN, ConfigKey::DIRECTORIES, $key]);
    }

    public function resolve(ContainerInterface $container): string
    {
        $directory = parent::resolve($container);
        return (string) realpath($directory);
    }
}
