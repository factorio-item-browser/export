<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Serializer;

use FactorioItemBrowser\Export\Constant\ConfigKey;
use FactorioItemBrowser\Export\Serializer\Handler\RawHandler;
use Interop\Container\ContainerInterface;
use JMS\Serializer\Handler\HandlerRegistry;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * The factory for the JMS serializer.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class SerializerFactory implements FactoryInterface
{
    /**
     * Creates the serializer.
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array<mixed>|null $options
     * @return SerializerInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): SerializerInterface
    {
        $builder = SerializerBuilder::create();
        $builder
            ->addMetadataDir(
                (string) realpath(__DIR__ . '/../../config/serializer'),
                'FactorioItemBrowser\Export'
            )
            ->addDefaultHandlers()
            ->configureHandlers(function (HandlerRegistry $registry): void {
                $registry->registerSubscribingHandler(new RawHandler());
            });

        $this->addCacheDirectory($container, $builder);

        return $builder->build();
    }

    /**
     * Adds the cache directory from the config to the builder.
     * @param ContainerInterface $container
     * @param SerializerBuilder $builder
     */
    protected function addCacheDirectory(ContainerInterface $container, SerializerBuilder $builder): void
    {
        $config = $container->get('config');
        $libraryConfig = $config[ConfigKey::PROJECT][ConfigKey::EXPORT] ?? [];
        $cacheDir = (string) ($libraryConfig[ConfigKey::DIRECTORIES][ConfigKey::DIRECTORY_CACHE] ?? '');

        if ($cacheDir !== '') {
            $builder->setCacheDir($cacheDir);
        }
    }
}
