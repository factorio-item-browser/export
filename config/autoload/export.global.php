<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export;

use FactorioItemBrowser\Export\Constant\ConfigKey;

/**
 * The configuration of the export scripts.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
return [
    ConfigKey::PROJECT => [
        ConfigKey::EXPORT => [
            ConfigKey::MOD_FILE_MANAGER_WORKING_DIRECTORY => __DIR__ . '/../../data/mods',
        ],
    ],


    'cache' => [
        'locale' => [
            'directory' => __DIR__ . '/../../data/cache/locale',
        ],
        'mod-file' => [
            'directory' => __DIR__ . '/../../data/cache/mod-file'
        ]
    ],
    'export-data' => [
        'raw' => [
            'directory' => __DIR__ . '/../../data/export/raw',
        ],
        'reduced' => [
            'directory' => __DIR__ . '/../../data/export/reduced',
        ]
    ],
    'factorio' => [
        'directory' => __DIR__ . '/../../factorio',
    ],
    'name' => 'Factorio Item Browser Export',
    'process-manager' => [
        'process-count' => 4,
        'poll-interval' => 100,
    ],
    'version' => '2.0.0-alpha'
];
