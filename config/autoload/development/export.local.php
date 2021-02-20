<?php

/**
 * The configuration of the export scripts.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */

declare(strict_types=1);

namespace FactorioItemBrowser\Export;

use FactorioItemBrowser\Export\Constant\ConfigKey;

return [
    ConfigKey::MAIN => [
        ConfigKey::DIRECTORIES => [
//            ConfigKey::DIRECTORY_CACHE => 'data/cache',
            ConfigKey::DIRECTORY_FACTORIO => 'data/factorio',
            ConfigKey::DIRECTORY_INSTANCES => 'data/instances',
            ConfigKey::DIRECTORY_LOGS => 'data/log',
            ConfigKey::DIRECTORY_MODS => 'data/mods',
            ConfigKey::DIRECTORY_TEMP => 'data/temp',
        ],
        ConfigKey::PARALLEL_DOWNLOADS => 4,
        ConfigKey::PARALLEL_RENDERS => 8,
        ConfigKey::UPLOAD_FTP => [
            ConfigKey::UPLOAD_FTP_HOST => 'fib-ftp',
            ConfigKey::UPLOAD_FTP_USERNAME => 'export',
            ConfigKey::UPLOAD_FTP_PASSWORD => 'export',
        ],
    ],
];
