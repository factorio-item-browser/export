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
use Imagine\Image\ImagineInterface;
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

            Factorio\DumpExtractor::class => InvokableFactory::class,
            Factorio\DumpInfoGenerator::class => Factorio\DumpInfoGeneratorFactory::class,
            Factorio\Instance::class => Factorio\InstanceFactory::class,

            I18n\Translator::class => I18n\TranslatorFactory::class,

            Merger\IconMerger::class => InvokableFactory::class,
            Merger\ItemMerger::class => Merger\ItemMergerFactory::class,
            Merger\MachineMerger::class => Merger\MachineMergerFactory::class,
            Merger\MergerManager::class => Merger\MergerManagerFactory::class,
            Merger\RecipeMerger::class => Merger\RecipeMergerFactory::class,

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

            Reducer\Combination\CombinationReducerManager::class => Reducer\Combination\CombinationReducerManagerFactory::class,
            Reducer\Combination\IconReducer::class => Reducer\Combination\IconReducerFactory::class,
            Reducer\Combination\ItemReducer::class => Reducer\Combination\ItemReducerFactory::class,
            Reducer\Combination\MachineReducer::class => Reducer\Combination\MachineReducerFactory::class,
            Reducer\Combination\RecipeReducer::class => Reducer\Combination\RecipeReducerFactory::class,
            Reducer\Mod\CombinationReducer::class => Reducer\Mod\CombinationReducerFactory::class,
            Reducer\Mod\ModReducerManager::class => Reducer\Mod\ModReducerManagerFactory::class,
            Reducer\Mod\ThumbnailReducer::class => Reducer\Mod\ThumbnailReducerFactory::class,

            Renderer\IconRenderer::class => Renderer\IconRendererFactory::class,

            // 3rd-party services
            ProcessManager::class => Process\ProcessManagerFactory::class,
            ImagineInterface::class => Renderer\ImagineFactory::class,
        ],
    ]
];
