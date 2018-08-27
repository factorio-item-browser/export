<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export;

/**
 * The configuration of the export scripts.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
return [
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
        'factorioDirectory' => __DIR__ . '/../../factorio',
        'modsDirectory' => __DIR__ . '/../../factorio/mods',
        'instancesDirectory' => __DIR__ . '/../../factorio/instances',
        'numberOfAttempts' => 2,
        'numberOfInstances' => 4
    ],
    'name' => 'Factorio Item Browser Export',
    'version' => '1.1.0'
];
