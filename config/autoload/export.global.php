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
        'directory' => __DIR__ . '/../../data/export'
    ],
    'factorio' => [
        'directory' => __DIR__ . '/../../factorio'
    ]
];
