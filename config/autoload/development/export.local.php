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
            ConfigKey::DIRECTORIES => [
//                ConfigKey::DIRECTORY_CACHE => __DIR__ . '/../../../data/cache',
                ConfigKey::DIRECTORY_FACTORIO => __DIR__ . '/../../../data/factorio',
                ConfigKey::DIRECTORY_INSTANCES => __DIR__ . '/../../../data/instances',
                ConfigKey::DIRECTORY_MODS => __DIR__ . '/../../../data/mods',
                ConfigKey::DIRECTORY_TEMP => __DIR__ . '/../../../data/temp',
            ],
            ConfigKey::PARALLEL_DOWNLOADS => 4,
            ConfigKey::PARALLEL_RENDERS => 8,
            ConfigKey::UPLOAD_FTP => [
                ConfigKey::UPLOAD_FTP_HOST => 'fib-ftp',
                ConfigKey::UPLOAD_FTP_USERNAME => 'export',
                ConfigKey::UPLOAD_FTP_PASSWORD => 'export',
            ],
        ],
    ],
];
