<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\AutoWire\Attribute;

use Attribute;
use BluePsyduck\LaminasAutoWireFactory\Attribute\ResolverAttribute;
use BluePsyduck\LaminasAutoWireFactory\Resolver\ResolverInterface;
use FactorioItemBrowser\Export\AutoWire\Resolver\ConfigDirectoryResolver;

/**
 * The attribute using the config directory resolver.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY)]
class ReadDirectoryFromConfig implements ResolverAttribute
{
    public function __construct(
        private readonly string $key
    ) {
    }

    public function createResolver(): ResolverInterface
    {
        return new ConfigDirectoryResolver($this->key);
    }
}
