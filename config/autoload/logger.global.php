<?php

/**
 * The configuration of the logger.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */

declare(strict_types=1);

namespace FactorioItemBrowser\Export;

use BluePsyduck\JmsSerializerFactory\Constant\ConfigKey as JmsConfigKey;
use FactorioItemBrowser\Export\Constant\ConfigKey;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;

return [
    'logger' => [
        'app' => [
            'name' => 'app',
            'handlers' => [
                [
                    'name' => StreamHandler::class,
                    'params' => [
                        'stream' => 'data/logs/app.log',
                    ],
                    'formatter' => [
                        'name' => LineFormatter::class,
                        'params' => [
                            'format' => "[%datetime%] %level_name%: %message% %context% %extra%\n",
                        ],
                    ],
                ],
            ],
        ],
    ],
];
