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

            Command\Clean\CleanCacheCommand::class => Command\Clean\CleanCacheCommandFactory::class,
            Command\Render\RenderIconCommand::class => Command\Render\RenderIconCommandFactory::class,
            Command\Update\UpdateListCommand::class => Command\Update\UpdateListCommandFactory::class,

            Command\ExportAllCommand::class => Command\ExportAllCommandFactory::class,
            Command\ExportModCommand::class => Command\ExportModCommandFactory::class,
            Command\ListAllCommand::class => Command\ListAllCommandFactory::class,
            Command\ListMissingCommand::class => Command\ListMissingCommandFactory::class,
            Command\ListUpdateCommand::class => Command\ListUpdateCommandFactory::class,

            ExportData\RawExportDataService::class => ExportData\RawExportDataServiceFactory::class,
            ExportData\ReducedExportDataService::class => ExportData\ReducedExportDataServiceFactory::class,

            Factorio\DumpExtractor::class => InvokableFactory::class,
            Factorio\FactorioManager::class => Factorio\FactorioManagerFactory::class,
            Factorio\Instance::class => Factorio\InstanceFactory::class,
            Factorio\Options::class => Factorio\OptionsFactory::class,

            I18n\LocaleFileReader::class => InvokableFactory::class,
            I18n\Translator::class => I18n\TranslatorFactory::class,

            Merger\IconMerger::class => InvokableFactory::class,
            Merger\ItemMerger::class => InvokableFactory::class,
            Merger\MachineMerger::class => InvokableFactory::class,
            Merger\MergerManager::class => Merger\MergerManagerFactory::class,
            Merger\RecipeMerger::class => InvokableFactory::class,

            Mod\CombinationCreator::class => Mod\CombinationCreatorFactory::class,
            Mod\DependencyResolver::class => Mod\DependencyResolverFactory::class,
            Mod\ModFileManager::class => Mod\ModFileManagerFactory::class,
            Mod\ParentCombinationFinder::class => Mod\ParentCombinationFinderFactory::class,

            ModFile\ModFileManager::class => ModFile\ModFileManagerFactory::class,
            ModFile\ModFileReader::class => ModFile\ModFileReaderFactory::class,

            Parser\IconParser::class => Parser\AbstractParserFactory::class,
            Parser\ItemParser::class => Parser\AbstractParserFactory::class,
            Parser\MachineParser::class => Parser\AbstractParserFactory::class,
            Parser\ParserManager::class => Parser\ParserManagerFactory::class,
            Parser\RecipeParser::class => Parser\AbstractParserFactory::class,

            Reducer\IconReducer::class => InvokableFactory::class,
            Reducer\ItemReducer::class => InvokableFactory::class,
            Reducer\MachineReducer::class => InvokableFactory::class,
            Reducer\RecipeReducer::class => InvokableFactory::class,
            Reducer\ReducerManager::class => Reducer\ReducerManagerFactory::class,

            Renderer\IconRenderer::class => Renderer\IconRendererFactory::class,
        ],
    ]
];
