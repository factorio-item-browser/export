<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export;

use FactorioItemBrowser\Export\Constant\CommandName;

/**
 * The configuration of the routes.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
return [
    'routes' => [
        [
            'name' => CommandName::CLEAN_CACHE,
            'route' => '[--mod=]',
            'handler' => Command\Clean\CleanCacheCommand::class,
            'short_description' => 'Cleans the caches.',
            'options_description' => [
                '--mod=<modName>' => 'The name of the mod to clean the cache for.',
            ],
        ],

        [
            'name' => CommandName::EXPORT_COMBINATION,
            'handler' => Command\Export\ExportCombinationCommand::class,
            'short_description' => 'Exports a combination of mods running the Factorio game.',
            'options_description' => [
                '<combinationHash>' => 'The hash of the combination to export.',
            ],
        ],
        [
            'name' => CommandName::EXPORT_MOD,
            'handler' => Command\Export\ExportModCommand::class,
            'short_description' => 'Exports a mod with all its combinations.',
            'options_description' => [
                '<modName>' => 'The name of the mod to be exported.',
            ],
        ],
        [
            'name' => CommandName::EXPORT_MOD_STEP,
            'handler' => Command\Export\ExportModStepCommand::class,
            'short_description' => 'Exports a step of the mod.',
            'options_description' => [
                '<modName>' => 'The name of the mod to be exported.',
                '<step>' => 'The step of the export.'
            ],
        ],

        [
            'name' => CommandName::LIST,
            'handler' => Command\Lists\ListCommand::class,
            'short_description' => 'Lists all available mods.',
        ],

        [
            'name' => CommandName::REDUCE_COMBINATION,
            'handler' => Command\Reduce\ReduceCombinationCommand::class,
            'short_description' => 'Reduces an exported combination against its parents.',
            'options_description' => [
                '<combinationHash>' => 'The hash of the combination to reduce.',
            ],
        ],

        [
            'name' => CommandName::RENDER_ICON,
            'handler' => Command\Render\RenderIconCommand::class,
            'short_description' => 'Renders an icon.',
            'options_description' => [
                '<iconHash>' => 'The hash of the icon to render.',
            ],
        ],
        [
            'name' => CommandName::RENDER_MOD_ICONS,
            'handler' => Command\Render\RenderModIconsCommand::class,
            'short_description' => 'Renders all icons of a mod.',
            'options_description' => [
                '<modName>' => 'The name of the mod to render the icons of.',
            ],
        ],

        [
            'name' => CommandName::UPDATE_DEPENDENCIES,
            'route' => '[--mod=]',
            'handler' => Command\Update\UpdateDependenciesCommand::class,
            'short_description' => 'Updates the dependencies of the mods.',
            'options_description' => [
                '--mod=<modName>' => 'The name of the mod to update the dependencies for.',
            ],
        ],
        [
            'name' => CommandName::UPDATE_LIST,
            'handler' => Command\Update\UpdateListCommand::class,
            'short_description' => 'Updates the list of mods from the directory.',
        ],
        [
            'name' => CommandName::UPDATE_ORDER,
            'handler' => Command\Update\UpdateOrderCommand::class,
            'short_description' => 'Updates the absolute order of the mods.',
        ],
    ]
];
