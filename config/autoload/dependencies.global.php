<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export;

/**
 * The configuration of the dependencies.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
use FactorioItemBrowser\ExportData\Service\ExportDataService;
use Zend\ServiceManager\Factory\InvokableFactory;

return [
    'dependencies' => [
        'factories'  => [
            ExportDataService::class => ExportData\ExportDataServiceFactory::class,

            Command\ExportModCommand::class => Command\ExportModCommandFactory::class,
            Command\ListAllCommand::class => Command\ListAllCommandFactory::class,
            Command\ListMissingCommand::class => Command\ListMissingCommandFactory::class,
            Command\ListUpdateCommand::class => Command\ListUpdateCommandFactory::class,
            Command\TestCommand::class => Command\TestCommandFactory::class,

            Factorio\DumpExtractor::class => InvokableFactory::class,
            Factorio\FactorioManager::class => Factorio\FactorioManagerFactory::class,
            Factorio\Instance::class => Factorio\InstanceFactory::class,
            Factorio\Options::class => Factorio\OptionsFactory::class,

            I18n\LocaleFileReader::class => InvokableFactory::class,
            I18n\Translator::class => I18n\TranslatorFactory::class,

            Merger\IconMerger::class => InvokableFactory::class,
            Merger\ItemMerger::class => InvokableFactory::class,
            Merger\MergerManager::class => Merger\MergerManagerFactory::class,
            Merger\RecipeMerger::class => InvokableFactory::class,

            Mod\CombinationCreator::class => Mod\CombinationCreatorFactory::class,
            Mod\DependencyResolver::class => Mod\DependencyResolverFactory::class,
            Mod\ModFileManager::class => Mod\ModFileManagerFactory::class,
            Mod\ParentCombinationFinder::class => Mod\ParentCombinationFinderFactory::class,

            Parser\IconParser::class => Parser\AbstractParserFactory::class,
            Parser\ItemParser::class => Parser\AbstractParserFactory::class,
            Parser\ParserManager::class => Parser\ParserManagerFactory::class,
            Parser\RecipeParser::class => Parser\AbstractParserFactory::class,

            Reducer\IconReducer::class => InvokableFactory::class,
            Reducer\ItemReducer::class => InvokableFactory::class,
            Reducer\RecipeReducer::class => InvokableFactory::class,
            Reducer\ReducerManager::class => Reducer\ReducerManagerFactory::class,

            Renderer\IconRenderer::class => Renderer\IconRendererFactory::class,
        ],
    ]
];
