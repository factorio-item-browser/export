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
            'name' => 'clean cache [--mod=<modName>]',
            'handler' => Command\Clean\CleanCacheCommand::class,
            'short_description' => 'Cleans the caches.',
            'options_description' => [
                '--mod=<modName>' => 'The name of the mod to clean the cache for.',
            ],
        ],
        [
            'name' => 'render icon <hash>',
            'handler' => Command\Render\RenderIconCommand::class,
            'short_description' => 'Renders an icon.',
            'options_description' => [
                '<hash>' => 'The hash of the icon to render.',
            ],
        ],
        [
            'name' => 'update list',
            'handler' => Command\Update\UpdateListCommand::class,
            'short_description' => 'Updates the list of mods from the directory.',
        ],





        [
            'name' => 'export mod <modName>',
            'handler' => Command\ExportModCommand::class,
            'short_description' => 'Exports all relevant combinations of the specified mod.',
            'options_descriptions' => [
                '<modName>' => 'The name of the mod to be exported.'
            ],
        ],
        [
            'name' => 'export all',
            'handler' => Command\ExportAllCommand::class,
            'short_description' => 'Exports ALL mods. Takes hours!',
        ],

        [
            'name' => 'list all',
            'handler' => Command\ListAllCommand::class,
            'short_description' => 'Lists all available mods.',
        ],
        [
            'name' => 'list missing',
            'handler' => Command\ListMissingCommand::class,
            'short_description' => 'Lists missing mods which are dependencies of other mods.',
        ],
        [
            'name' => 'list update',
            'handler' => Command\ListUpdateCommand::class,
            'short_description' => 'Updates the list of mods from the zip files.',
        ],
    ]
];