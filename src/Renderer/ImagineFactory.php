<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Renderer;

use Imagine\Gd\Imagine;
use Imagine\Image\ImagineInterface;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * The factory for the imagine class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ImagineFactory implements FactoryInterface
{
    /**
     * Creates the icon renderer.
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  array<mixed>|null $options
     * @return ImagineInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ImagineInterface
    {
        return new Imagine();
    }
}
