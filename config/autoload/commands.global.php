<?php

declare(strict_types=1);

/**
 * The file providing the commands.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */

namespace FactorioItemBrowser\Export;

use FactorioItemBrowser\Export\Constant\CommandName;

return [
    'commands' => [
        CommandName::PROCESS => Command\ProcessCommand::class,
        CommandName::RENDER_ICON => Command\RenderIconCommand::class,
    ],
];
