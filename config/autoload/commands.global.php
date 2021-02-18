<?php

/**
 * The file providing the commands.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */

declare(strict_types=1);

namespace FactorioItemBrowser\Export;

use FactorioItemBrowser\Export\Constant\CommandName;

return [
    'commands' => [
        CommandName::DOWNLOAD_FACTORIO => Command\DownloadFactorioCommand::class,
        CommandName::PROCESS => Command\ProcessCommand::class,
    ],
];
