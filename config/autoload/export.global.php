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
        ConfigKey::OUTPUT_PROCESSORS => [
            OutputProcessor\ConsoleOutputProcessor::class,
            OutputProcessor\DumpOutputProcessor::class,
            OutputProcessor\ErrorOutputProcessor::class,
            OutputProcessor\ModNameOutputProcessor::class,
        ],
        ConfigKey::OUTPUT_DUMP_PROCESSORS => [
            OutputProcessor\DumpProcessor\ExpensiveRecipeDumpProcessor::class,
            OutputProcessor\DumpProcessor\FluidDumpProcessor::class,
            OutputProcessor\DumpProcessor\IconDumpProcessor::class,
            OutputProcessor\DumpProcessor\ItemDumpProcessor::class,
            OutputProcessor\DumpProcessor\MachineDumpProcessor::class,
            OutputProcessor\DumpProcessor\NormalRecipeDumpProcessor::class,
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
        ConfigKey::RENDER_ICON_BINARY => 'bin/render-icon',
        ConfigKey::SERIALIZER => [
            JmsConfigKey::ADD_DEFAULT_HANDLERS => true,
            JmsConfigKey::HANDLERS => [
                Serializer\Handler\ConstructorHandler::class,
                Serializer\Handler\RawHandler::class,
            ],
        ],
    ],
];
