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
                ConfigKey::DIRECTORY_CACHE => __DIR__ . '/../../data/cache',
                ConfigKey::DIRECTORY_INSTANCES => __DIR__ . '/../../data/instances',
                ConfigKey::DIRECTORY_MODS => __DIR__ . '/../../data/mods',
                ConfigKey::DIRECTORY_TEMP => __DIR__ . '/../../data/temp',
            ],
            ConfigKey::PARSERS => [
                Parser\IconParser::class,
                Parser\ItemParser::class,
                Parser\MachineParser::class,
                Parser\ModParser::class,
                Parser\RecipeParser::class,
                Parser\TranslationParser::class,
            ],
            ConfigKey::PROCESS_STEPS => [
                Command\ProcessStep\DownloadStep::class,
                Command\ProcessStep\FactorioStep::class,
                Command\ProcessStep\ParserStep::class,
                Command\ProcessStep\RenderIconsStep::class,
                Command\ProcessStep\UploadStep::class,
                Command\ProcessStep\DoneStep::class,
            ],
        ],
    ],
];
