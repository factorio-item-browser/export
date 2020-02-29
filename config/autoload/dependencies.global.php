<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export;

/**
 * The configuration of the dependencies.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */

use BluePsyduck\FactorioModPortalClient\Constant\ConfigKey as ModConfigKey;
use BluePsyduck\LaminasAutoWireFactory\AutoWireFactory;
use FactorioItemBrowser\Export\Constant\ConfigKey;
use Imagine\Image\ImagineInterface;
use JMS\Serializer\SerializerInterface;
use Laminas\I18n\Translator\Translator;
use Laminas\I18n\Translator\TranslatorInterface;

use Symfony\Component\Filesystem\Filesystem;
use function BluePsyduck\LaminasAutoWireFactory\injectAliasArray;
use function BluePsyduck\LaminasAutoWireFactory\readConfig;

return [
    'dependencies' => [
        'aliases' => [
            Translator::class . ' $placeholderTranslator' => TranslatorInterface::class
        ],
        'factories' => [
            Command\DownloadFactorioCommand::class => AutoWireFactory::class,
            Command\ProcessCommand::class => AutoWireFactory::class,
            Command\ProcessStep\DoneStep::class => AutoWireFactory::class,
            Command\ProcessStep\DownloadStep::class => AutoWireFactory::class,
            Command\ProcessStep\FactorioStep::class => AutoWireFactory::class,
            Command\ProcessStep\ParserStep::class => AutoWireFactory::class,
            Command\ProcessStep\RenderIconsStep::class => AutoWireFactory::class,
            Command\ProcessStep\UploadStep::class => AutoWireFactory::class,
            Command\RenderIconCommand::class => AutoWireFactory::class,

            Console\Console::class => AutoWireFactory::class,

            Factorio\DumpExtractor::class => AutoWireFactory::class,
            Factorio\FactorioDownloader::class => AutoWireFactory::class,
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
            Parser\RecipeParser::class => AutoWireFactory::class,
            Parser\TranslationParser::class => AutoWireFactory::class,

            Renderer\IconRenderer::class => AutoWireFactory::class,

            // 3rd-party services
            Filesystem::class => AutoWireFactory::class,
            ImagineInterface::class => Renderer\ImagineFactory::class,
            SerializerInterface::class . ' $exportSerializer' => Serializer\SerializerFactory::class,

            // Auto-wire helpers
            'array $exportParsers' => injectAliasArray(ConfigKey::PROJECT, ConfigKey::EXPORT, ConfigKey::PARSERS),
            'array $exportProcessSteps' => injectAliasArray(ConfigKey::PROJECT, ConfigKey::EXPORT, ConfigKey::PROCESS_STEPS),

            'bool $isDebug' => readConfig('debug'),

            'int $numberOfParallelDownloads' => readConfig(ConfigKey::PROJECT, ConfigKey::EXPORT, ConfigKey::PARALLEL_DOWNLOADS),
            'int $numberOfParallelRenderProcesses' => readConfig(ConfigKey::PROJECT, ConfigKey::EXPORT, ConfigKey::PARALLEL_RENDERS),

            'string $factorioDirectory' => readConfig(ConfigKey::PROJECT, ConfigKey::EXPORT, ConfigKey::DIRECTORIES, ConfigKey::DIRECTORY_FACTORIO),
            'string $factorioDownloadToken' => readConfig(ModConfigKey::MAIN, ModConfigKey::OPTIONS, ModConfigKey::OPTION_TOKEN),
            'string $factorioDownloadUsername' => readConfig(ModConfigKey::MAIN, ModConfigKey::OPTIONS, ModConfigKey::OPTION_USERNAME),
            'string $instancesDirectory' => readConfig(ConfigKey::PROJECT, ConfigKey::EXPORT, ConfigKey::DIRECTORIES, ConfigKey::DIRECTORY_INSTANCES),
            'string $modsDirectory' => readConfig(ConfigKey::PROJECT, ConfigKey::EXPORT, ConfigKey::DIRECTORIES, ConfigKey::DIRECTORY_MODS),
            'string $tempDirectory' => readConfig(ConfigKey::PROJECT, ConfigKey::EXPORT, ConfigKey::DIRECTORIES, ConfigKey::DIRECTORY_TEMP),
            'string $uploadFtpHost' => readConfig(ConfigKey::PROJECT, ConfigKey::EXPORT, ConfigKey::UPLOAD_FTP, ConfigKey::UPLOAD_FTP_HOST),
            'string $uploadFtpUsername' => readConfig(ConfigKey::PROJECT, ConfigKey::EXPORT, ConfigKey::UPLOAD_FTP, ConfigKey::UPLOAD_FTP_USERNAME),
            'string $uploadFtpPassword' => readConfig(ConfigKey::PROJECT, ConfigKey::EXPORT, ConfigKey::UPLOAD_FTP, ConfigKey::UPLOAD_FTP_PASSWORD),
        ],
    ],
];
