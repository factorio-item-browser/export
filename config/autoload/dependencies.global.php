<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export;

/**
 * The configuration of the dependencies.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */

use BluePsyduck\SymfonyProcessManager\ProcessManager;
use BluePsyduck\ZendAutoWireFactory\AutoWireFactory;
use FactorioItemBrowser\Export\Constant\ConfigKey;
use Imagine\Image\ImagineInterface;
use JMS\Serializer\SerializerInterface;
use Zend\Console\Adapter\AdapterInterface;
use Zend\Console\Console as ZendConsole;
use Zend\I18n\Translator\Translator;
use Zend\I18n\Translator\TranslatorInterface;
use Zend\ServiceManager\Factory\InvokableFactory;
use function BluePsyduck\ZendAutoWireFactory\injectAliasArray;
use function BluePsyduck\ZendAutoWireFactory\readConfig;

return [
    'dependencies' => [
        'aliases' => [
            Translator::class . ' $placeholderTranslator' => TranslatorInterface::class
        ],
        'factories' => [
            Console\Console::class => AutoWireFactory::class,

            Factorio\DumpExtractor::class => AutoWireFactory::class,

            Helper\HashingHelper::class => AutoWireFactory::class,

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



            Command\Clean\CleanCacheCommand::class => Command\Clean\CleanCacheCommandFactory::class,
            Command\Export\ExportCombinationCommand::class => Command\Export\ExportCombinationCommandFactory::class,
            Command\Export\ExportModCommand::class => Command\Export\ExportModCommandFactory::class,
            Command\Export\ExportModWithDependenciesCommand::class => Command\Export\ExportModWithDependenciesCommandFactory::class,
            Command\Export\ExportModMetaCommand::class => Command\Export\ExportModMetaCommandFactory::class,
            Command\Export\ExportModStepCommand::class => Command\Export\ExportModStepCommandFactory::class,
            Command\Export\ExportModThumbnailCommand::class => Command\Export\ExportModThumbnailCommandFactory::class,
            Command\Export\ExportPrepareCommand::class => Command\Export\ExportPrepareCommandFactory::class,
            Command\Lists\ListCommand::class => Command\Lists\ListCommandFactory::class,
            Command\Lists\ListMissingCommand::class => Command\Lists\ListMissingCommandFactory::class,
            Command\Reduce\ReduceCombinationCommand::class => Command\Reduce\ReduceCombinationCommandFactory::class,
            Command\Reduce\ReduceModCommand::class => Command\Reduce\ReduceModCommandFactory::class,
            Command\Render\RenderIconCommand::class => Command\Render\RenderIconCommandFactory::class,
            Command\Render\RenderModIconsCommand::class => Command\Render\RenderModIconsCommandFactory::class,
            Command\Update\UpdateDependenciesCommand::class => Command\Update\UpdateDependenciesCommandFactory::class,
            Command\Update\UpdateListCommand::class => Command\Update\UpdateListCommandFactory::class,
            Command\Update\UpdateOrderCommand::class => Command\Update\UpdateOrderCommandFactory::class,

            Factorio\DumpInfoGenerator::class => Factorio\DumpInfoGeneratorFactory::class,
            Factorio\Instance::class => Factorio\InstanceFactory::class,

            // 3rd-party services
            ImagineInterface::class => Renderer\ImagineFactory::class,
            ProcessManager::class => Process\ProcessManagerFactory::class,
            SerializerInterface::class . ' $exportSerializer' => Serializer\SerializerFactory::class,

            // Auto-wire helpers
            'array $exportParsers' => injectAliasArray(ConfigKey::PROJECT, ConfigKey::EXPORT, ConfigKey::PARSERS),
            'int $numberOfParallelDownloads' => readConfig(ConfigKey::PROJECT, ConfigKey::EXPORT, ConfigKey::PARALLEL_DOWNLOADS),
            'string $modsDirectory' => readConfig(ConfigKey::PROJECT, ConfigKey::EXPORT, ConfigKey::DIRECTORIES, ConfigKey::DIRECTORY_MODS),
            'string $tempDirectory' => readConfig(ConfigKey::PROJECT, ConfigKey::EXPORT, ConfigKey::DIRECTORIES, ConfigKey::DIRECTORY_TEMP),
        ],
        'services' => [
            AdapterInterface::class => ZendConsole::getInstance(),
        ],
    ]
];
