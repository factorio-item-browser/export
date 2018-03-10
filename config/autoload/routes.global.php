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
            'name' => 'list all',
            'handler' => Command\ListAllCommand::class,
            'short_description' => 'Lists all available mods.',
        ],
        [
            'name' => 'list missing',
            'handler' => Command\ListMissingCommand::class,
            'short_description' => 'Lists missing mods which are dependencies of other mods.',
        ],
    ]
];