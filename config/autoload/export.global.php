<?php

/**
 * The configuration of the export scripts.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */

declare(strict_types=1);

namespace FactorioItemBrowser\Export;

use BluePsyduck\JmsSerializerFactory\Constant\ConfigKey as JmsConfigKey;
use FactorioItemBrowser\Export\Constant\ConfigKey;

return [
    ConfigKey::MAIN => [
        ConfigKey::DATA_PROCESSORS => [
            DataProcessor\ModInfoAdder::class,
            DataProcessor\ModThumbnailAdder::class,
            DataProcessor\ExpensiveRecipeFilter::class,
            DataProcessor\TranslationLoader::class,
            DataProcessor\Translator::class,
            DataProcessor\IconAssigner::class,
            DataProcessor\UnusedIconFilter::class,
            DataProcessor\IconRenderer::class,
        ],
        ConfigKey::OUTPUT_PROCESSORS => [
            OutputProcessor\ConsoleOutputProcessor::class,
            OutputProcessor\DumpOutputProcessor::class,
            OutputProcessor\ErrorOutputProcessor::class,
            OutputProcessor\ModNameOutputProcessor::class,
        ],
        ConfigKey::PROCESS_STEPS => [
            Command\ProcessStep\DownloadStep::class,
            Command\ProcessStep\FactorioStep::class,
            Command\ProcessStep\DataProcessorStep::class,
            Command\ProcessStep\UploadStep::class,
            Command\ProcessStep\DoneStep::class,
        ],
        ConfigKey::RENDER_ICON_BINARY => 'bin/render-icon',
        ConfigKey::SERIALIZER => [
            JmsConfigKey::ADD_DEFAULT_HANDLERS => true,
            JmsConfigKey::HANDLERS => [
                Serializer\Handler\ConstructorHandler::class,
            ],
        ],
    ],
];
