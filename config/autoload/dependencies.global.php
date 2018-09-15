<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export;

/**
 * The configuration of the dependencies.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
use Zend\ServiceManager\Factory\InvokableFactory;

return [
    'dependencies' => [
        'factories'  => [
            Cache\LocaleCache::class => Cache\LocaleCacheFactory::class,
            Cache\ModFileCache::class => Cache\ModFileCacheFactory::class,

            Combination\CombinationCreator::class => Combination\CombinationCreatorFactory::class,
            Combination\ParentCombinationFinder::class => Combination\ParentCombinationFinderFactory::class,

            Command\Clean\CleanCacheCommand::class => Command\Clean\CleanCacheCommandFactory::class,
            Command\Export\ExportCombinationCommand::class => Command\Export\ExportCombinationCommandFactory::class,
            Command\Export\ExportReduceCommand::class => Command\Export\ExportReduceCommandFactory::class,
            Command\Lists\ListCommand::class => Command\Lists\ListCommandFactory::class,
            Command\Render\RenderIconCommand::class => Command\Render\RenderIconCommandFactory::class,
            Command\Render\RenderModIconsCommand::class => Command\Render\RenderModIconsCommandFactory::class,
            Command\Update\UpdateDependenciesCommand::class => Command\Update\UpdateDependenciesCommandFactory::class,
            Command\Update\UpdateListCommand::class => Command\Update\UpdateListCommandFactory::class,
            Command\Update\UpdateOrderCommand::class => Command\Update\UpdateOrderCommandFactory::class,

            ExportData\RawExportDataService::class => ExportData\RawExportDataServiceFactory::class,
            ExportData\ReducedExportDataService::class => ExportData\ReducedExportDataServiceFactory::class,

            Factorio\DumpExtractor::class => InvokableFactory::class,
            Factorio\FactorioManager::class => Factorio\FactorioManagerFactory::class,
            Factorio\Instance::class => Factorio\InstanceFactory::class,
            Factorio\Options::class => Factorio\OptionsFactory::class,

            I18n\Translator::class => I18n\TranslatorFactory::class,

            Merger\IconMerger::class => InvokableFactory::class,
            Merger\ItemMerger::class => InvokableFactory::class,
            Merger\MachineMerger::class => InvokableFactory::class,
            Merger\MergerManager::class => Merger\MergerManagerFactory::class,
            Merger\RecipeMerger::class => InvokableFactory::class,

            Mod\DependencyReader::class => Mod\DependencyReaderFactory::class,
            Mod\DependencyResolver::class => Mod\DependencyResolverFactory::class,
            Mod\LocaleReader::class => Mod\LocaleReaderFactory::class,
            Mod\ModFileManager::class => Mod\ModFileManagerFactory::class,
            Mod\ModReader::class => Mod\ModReaderFactory::class,

            Parser\IconParser::class => Parser\IconParserFactory::class,
            Parser\ItemParser::class => Parser\ItemParserFactory::class,
            Parser\MachineParser::class => Parser\MachineParserFactory::class,
            Parser\ParserManager::class => Parser\ParserManagerFactory::class,
            Parser\RecipeParser::class => Parser\RecipeParserFactory::class,

            Reducer\IconReducer::class => Reducer\IconReducerFactory::class,
            Reducer\ItemReducer::class => Reducer\ItemReducerFactory::class,
            Reducer\MachineReducer::class => Reducer\MachineReducerFactory::class,
            Reducer\RecipeReducer::class => Reducer\RecipeReducerFactory::class,
            Reducer\ReducerManager::class => Reducer\ReducerManagerFactory::class,

            Renderer\IconRenderer::class => Renderer\IconRendererFactory::class,
        ],
    ]
];
