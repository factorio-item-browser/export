<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export;

/**
 * The configuration of the dependencies.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */

use BluePsyduck\ZendAutoWireFactory\AutoWireFactory;
use FactorioItemBrowser\Export\Constant\ConfigKey;
use Imagine\Image\ImagineInterface;
use JMS\Serializer\SerializerInterface;
use Zend\Console\Adapter\AdapterInterface;
use Zend\Console\Console as ZendConsole;
use Zend\I18n\Translator\Translator;
use Zend\I18n\Translator\TranslatorInterface;
use function BluePsyduck\ZendAutoWireFactory\injectAliasArray;
use function BluePsyduck\ZendAutoWireFactory\readConfig;

return [
    'dependencies' => [
        'aliases' => [
            Translator::class . ' $placeholderTranslator' => TranslatorInterface::class
        ],
        'factories' => [
            Command\ProcessCommand::class => AutoWireFactory::class,

            Console\Console::class => AutoWireFactory::class,

            Factorio\DumpExtractor::class => AutoWireFactory::class,
            Factorio\Instance::class => AutoWireFactory::class,

            Helper\HashCalculator::class => AutoWireFactory::class,

            I18n\LocaleReader::class => AutoWireFactory::class,
            I18n\Translator::class => AutoWireFactory::class,

            Mod\ModDownloader::class => AutoWireFactory::class,
            Mod\ModFileManager::class => AutoWireFactory::class,

            Parser\IconParser::class => AutoWireFactory::class,
            Parser\ItemParser::class => AutoWireFactory::class,
            Parser\MachineParser::class => AutoWireFactory::class,
            Parser\ModParser::class => AutoWireFactory::class,
            Parser\ParserManager::class => AutoWireFactory::class,
            Parser\RecipeParser::class => AutoWireFactory::class,
            Parser\TranslationParser::class => AutoWireFactory::class,

            Renderer\IconRenderer::class => AutoWireFactory::class,

            // 3rd-party services
            ImagineInterface::class => Renderer\ImagineFactory::class,
            SerializerInterface::class . ' $exportSerializer' => Serializer\SerializerFactory::class,

            // Auto-wire helpers
            'array $exportParsers' => injectAliasArray(ConfigKey::PROJECT, ConfigKey::EXPORT, ConfigKey::PARSERS),

            'int $numberOfParallelDownloads' => readConfig(ConfigKey::PROJECT, ConfigKey::EXPORT, ConfigKey::PARALLEL_DOWNLOADS),

            'string $factorioDirectory' => readConfig(ConfigKey::PROJECT, ConfigKey::EXPORT, ConfigKey::DIRECTORIES, ConfigKey::DIRECTORY_FACTORIO),
            'string $instancesDirectory' => readConfig(ConfigKey::PROJECT, ConfigKey::EXPORT, ConfigKey::DIRECTORIES, ConfigKey::DIRECTORY_INSTANCES),
            'string $modsDirectory' => readConfig(ConfigKey::PROJECT, ConfigKey::EXPORT, ConfigKey::DIRECTORIES, ConfigKey::DIRECTORY_MODS),
            'string $tempDirectory' => readConfig(ConfigKey::PROJECT, ConfigKey::EXPORT, ConfigKey::DIRECTORIES, ConfigKey::DIRECTORY_TEMP),
        ],
        'services' => [
            AdapterInterface::class => ZendConsole::getInstance(),
        ],
    ]
];
