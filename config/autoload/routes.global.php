<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export;

/**
 * The configuration of the routes.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
return [
    'routes' => [
        [
            'name' => 'clean cache',
            'route' => '[--mod=]',
            'handler' => Command\Clean\CleanCacheCommand::class,
            'short_description' => 'Cleans the caches.',
            'options_description' => [
                '--mod=<modName>' => 'The name of the mod to clean the cache for.',
            ],
        ],

        [
            'name' => 'export combination',
            'route' => '<combinationHash>',
            'handler' => Command\Export\ExportCombinationCommand::class,
            'short_description' => 'Exports a combination of mods running the Factorio game.',
            'options_description' => [
                '<combinationHash>' => 'The hash of the combination to export.',
            ],
        ],
        [
            'name' => 'export reduce',
            'route' => '<combinationHash>',
            'handler' => Command\Export\ExportReduceCommand::class,
            'short_description' => 'Reduces an exported combination against its parents.',
            'options_description' => [
                '<combinationHash>' => 'The hash of the combination to reduce.',
            ],
        ],

        [
            'name' => 'list',
            'handler' => Command\Lists\ListCommand::class,
            'short_description' => 'Lists all available mods.',
        ],

        [
            'name' => 'render icon',
            'route' => '<hash>',
            'handler' => Command\Render\RenderIconCommand::class,
            'short_description' => 'Renders an icon.',
            'options_description' => [
                '<hash>' => 'The hash of the icon to render.',
            ],
        ],
        [
            'name' => 'render mod-icons',
            'route' => '<modName>',
            'handler' => Command\Render\RenderModIconsCommand::class,
            'short_description' => 'Renders all icons of a mod.',
            'options_description' => [
                '<modName>' => 'The name of the mod to render the icons of.',
            ],
        ],

        [
            'name' => 'update dependencies',
            'route' => '[--mod=]',
            'handler' => Command\Update\UpdateDependenciesCommand::class,
            'short_description' => 'Updates the dependencies of the mods.',
            'options_description' => [
                '--mod=<modName>' => 'The name of the mod to update the dependencies for.',
            ],
        ],
        [
            'name' => 'update list',
            'handler' => Command\Update\UpdateListCommand::class,
            'short_description' => 'Updates the list of mods from the directory.',
        ],
        [
            'name' => 'update order',
            'handler' => Command\Update\UpdateOrderCommand::class,
            'short_description' => 'Updates the absolute order of the mods.',
        ],
    ]
];