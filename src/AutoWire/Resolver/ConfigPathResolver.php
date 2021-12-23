<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\AutoWire\Resolver;

use BluePsyduck\LaminasAutoWireFactory\Resolver\ConfigResolver;
use FactorioItemBrowser\Export\Constant\ConfigKey;
use Psr\Container\ContainerInterface;

/**
 * The resolver for reading a path from the config.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ConfigPathResolver extends ConfigResolver
{
    public function resolve(ContainerInterface $container): string
    {
        $directory = strval(parent::resolve($container));
        return (string) realpath($directory);
    }
}
