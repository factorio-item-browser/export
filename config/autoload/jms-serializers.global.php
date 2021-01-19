<?php

/**
 * The configuration of the JMS serializers.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */

declare(strict_types=1);

namespace FactorioItemBrowser\Export;

use BluePsyduck\JmsSerializerFactory\Constant\ConfigKey;

return [
    'jms-serializers' => [
        'export' => [
//            ConfigKey::CACHE_DIR => __DIR__ . '/../../data/cache/serializer',
            ConfigKey::METADATA_DIRS => [
                'FactorioItemBrowser\Export' => __DIR__ . '/../serializer',
            ],
            ConfigKey::ADD_DEFAULT_HANDLERS => true,
            ConfigKey::HANDLERS => [
                Serializer\Handler\ConstructorHandler::class,
                Serializer\Handler\RawHandler::class,
            ],
        ],
    ],
];
