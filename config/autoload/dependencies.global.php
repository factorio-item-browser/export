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




            Cache\ModFileCache::class => Cache\ModFileCacheFactory::class,

            Combination\CombinationCreator::class => Combination\CombinationCreatorFactory::class,
            Combination\ParentCombinationFinder::class => Combination\ParentCombinationFinderFactory::class,

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

            ExportData\RawExportDataService::class => ExportData\RawExportDataServiceFactory::class,
            ExportData\ReducedExportDataService::class => ExportData\ReducedExportDataServiceFactory::class,

            Factorio\DumpInfoGenerator::class => Factorio\DumpInfoGeneratorFactory::class,
            Factorio\Instance::class => Factorio\InstanceFactory::class,

            Merger\IconMerger::class => InvokableFactory::class,
            Merger\ItemMerger::class => Merger\ItemMergerFactory::class,
            Merger\MachineMerger::class => Merger\MachineMergerFactory::class,
            Merger\MergerManager::class => Merger\MergerManagerFactory::class,
            Merger\RecipeMerger::class => Merger\RecipeMergerFactory::class,

            Mod\DependencyReader::class => Mod\DependencyReaderFactory::class,
            Mod\DependencyResolver::class => Mod\DependencyResolverFactory::class,
            Mod\ModReader::class => Mod\ModReaderFactory::class,

            Reducer\Combination\CombinationReducerManager::class => Reducer\Combination\CombinationReducerManagerFactory::class,
            Reducer\Combination\IconReducer::class => Reducer\Combination\IconReducerFactory::class,
            Reducer\Combination\ItemReducer::class => Reducer\Combination\ItemReducerFactory::class,
            Reducer\Combination\MachineReducer::class => Reducer\Combination\MachineReducerFactory::class,
            Reducer\Combination\RecipeReducer::class => Reducer\Combination\RecipeReducerFactory::class,
            Reducer\Mod\CombinationReducer::class => Reducer\Mod\CombinationReducerFactory::class,
            Reducer\Mod\ModReducerManager::class => Reducer\Mod\ModReducerManagerFactory::class,
            Reducer\Mod\ThumbnailReducer::class => Reducer\Mod\ThumbnailReducerFactory::class,

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
