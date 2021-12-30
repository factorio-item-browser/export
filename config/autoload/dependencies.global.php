<?php

/**
 * The configuration of the dependencies.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */

declare(strict_types=1);

namespace FactorioItemBrowser\Export;

use Blazon\PSR11FlySystem\FlySystemFactory;
use BluePsyduck\FactorioTranslator\Translator as FactorioTranslator;
use BluePsyduck\JmsSerializerFactory\JmsSerializerFactory;
use BluePsyduck\LaminasAutoWireFactory\AutoWireFactory;
use FactorioItemBrowser\Export\Constant\ConfigKey;
use FactorioItemBrowser\Export\Constant\ServiceName;
use MonologFactory\DiContainerLoggerFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

return [
    'dependencies' => [
        'aliases' => [
            OutputInterface::class => ConsoleOutputInterface::class,
        ],
        'factories' => [
            Command\DownloadFactorioCommand::class => AutoWireFactory::class,
            Command\ProcessCommand::class => AutoWireFactory::class,
            Command\ProcessStep\DataProcessorStep::class => AutoWireFactory::class,
            Command\ProcessStep\DoneStep::class => AutoWireFactory::class,
            Command\ProcessStep\DownloadStep::class => AutoWireFactory::class,
            Command\ProcessStep\FactorioStep::class => AutoWireFactory::class,
            Command\ProcessStep\RenderIconsStep::class => AutoWireFactory::class,
            Command\ProcessStep\UploadStep::class => AutoWireFactory::class,
            Command\UpdateFactorioCommand::class => AutoWireFactory::class,

            DataProcessor\ExpensiveRecipeFilter::class => AutoWireFactory::class,
            DataProcessor\IconAssigner::class => AutoWireFactory::class,
            DataProcessor\ModInfoAdder::class => AutoWireFactory::class,
            DataProcessor\ModThumbnailAdder::class => AutoWireFactory::class,
            DataProcessor\TranslationLoader::class => AutoWireFactory::class,
            DataProcessor\Translator::class => AutoWireFactory::class,
            DataProcessor\UnusedIconFilter::class => AutoWireFactory::class,

            Helper\HashCalculator::class => AutoWireFactory::class,
            Helper\ZipArchiveExtractor::class => AutoWireFactory::class,

            Output\Console::class => AutoWireFactory::class,

            OutputProcessor\ConsoleOutputProcessor::class => AutoWireFactory::class,
            OutputProcessor\DumpOutputProcessor::class => AutoWireFactory::class,
            OutputProcessor\ErrorOutputProcessor::class => AutoWireFactory::class,
            OutputProcessor\ModNameOutputProcessor::class => AutoWireFactory::class,

            Process\FactorioProcessFactory::class => AutoWireFactory::class,
            Process\ModDownloadProcessFactory::class => AutoWireFactory::class,
            Process\ModDownloadProcessManager::class => AutoWireFactory::class,
            Process\RenderIconProcessFactory::class => AutoWireFactory::class,

            Serializer\Handler\ConstructorHandler::class => AutoWireFactory::class,
            Serializer\Handler\RawHandler::class => AutoWireFactory::class,

            Service\FactorioDownloadService::class => AutoWireFactory::class,
            Service\FactorioExecutionService::class => AutoWireFactory::class,
            Service\ModDownloadService::class => AutoWireFactory::class,
            Service\ModFileService::class => AutoWireFactory::class,

            ServiceName::FLYSYSTEM_UPLOAD => [FlySystemFactory::class, 'upload'],
            ServiceName::SERIALIZER => new JmsSerializerFactory(ConfigKey::MAIN, ConfigKey::SERIALIZER),

            // 3rd-party services
            ConsoleOutputInterface::class => Output\ConsoleOutputFactory::class,
            Filesystem::class => AutoWireFactory::class,
            LoggerInterface::class => [DiContainerLoggerFactory::class, 'app'],
            FactorioTranslator::class => Translator\TranslatorFactory::class,
        ],
    ],
];
