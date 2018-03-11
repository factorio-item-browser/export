<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Renderer;

use FactorioItemBrowser\Export\Mod\ModFileManager;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * The factory of the icon renderer.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class IconRendererFactory implements FactoryInterface
{
    /**
     * Creates the icon renderer.
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return IconRenderer
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var ModFileManager $modFileManager */
        $modFileManager = $container->get(ModFileManager::class);

        return new IconRenderer($modFileManager);
    }
}