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
                ConfigKey::DIRECTORY_FACTORIO => '/factorio',
            ],
            ConfigKey::PARALLEL_DOWNLOADS => 4,
            ConfigKey::PARALLEL_RENDERS => 8,
            ConfigKey::UPLOAD_FTP => [
                ConfigKey::UPLOAD_FTP_HOST => 'fib-ex-ftp',
                ConfigKey::UPLOAD_FTP_USERNAME => 'development',
                ConfigKey::UPLOAD_FTP_PASSWORD => 'factorio-item-browser',
            ],
        ],
    ],
];
