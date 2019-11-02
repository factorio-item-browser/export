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
            'name' => 'process',
            'handler' => Command\ProcessCommand::class,
            'short_description' => 'Processes the next job ready in the importer.',
        ],
        [
            'name' => 'render-icon <icon>',
            'handler' => Command\RenderIconCommand::class,
            'short_description' => 'Renders the specified icon.',
        ]
    ]
];
