<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Translator;

use BluePsyduck\FactorioTranslator\Loader\ModDirectoryLoader;
use BluePsyduck\FactorioTranslator\Processor\Placeholder\EntityPlaceholderProcessor;
use BluePsyduck\FactorioTranslator\Processor\Placeholder\ItemPlaceholderProcessor;
use BluePsyduck\FactorioTranslator\Processor\Placeholder\PositionPlaceholderProcessor;
use BluePsyduck\FactorioTranslator\Translator;
use FactorioItemBrowser\Export\Translator\Processor\ControlPlaceholderProcessor;
use FactorioItemBrowser\Export\Translator\Processor\RichTextEraser;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * The factory of the translator.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class TranslatorFactory implements FactoryInterface
{
    /**
     * Creates the translator.
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array<mixed>|null $options
     * @return Translator
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): Translator
    {
        $translator = new Translator();
        $translator->addLoader(new ModDirectoryLoader());

        $translator->addProcessor(new PositionPlaceholderProcessor())
                   ->addProcessor(new ItemPlaceholderProcessor())
                   ->addProcessor(new EntityPlaceholderProcessor())
                   ->addProcessor(new ControlPlaceholderProcessor())
                   ->addProcessor(new RichTextEraser());

        return $translator;
    }
}
