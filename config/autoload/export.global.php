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
    'exportData' => [
        'directory' => __DIR__ . '/../../data/export',
        'localeCacheDirectory' => __DIR__ . '/../../data/cache/locale'
    ],
    'factorio' => [
        'factorioDirectory' => __DIR__ . '/../../factorio',
        'modsDirectory' => __DIR__ . '/../../factorio/mods',
        'instancesDirectory' => __DIR__ . '/../../factorio/instances',
        'numberOfAttempts' => 2,
        'numberOfInstances' => 4
    ]
];
