<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export;

/**
 * The configuration of the dependencies.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
use FactorioItemBrowser\ExportData\Service\ExportDataService;

return [
    'dependencies' => [
        'factories'  => [
            ExportDataService::class => ExportData\ExportDataServiceFactory::class,

            Command\ListAllCommand::class => Command\ListAllCommandFactory::class,
            Command\ListMissingCommand::class => Command\ListMissingCommandFactory::class,
        ],
    ]
];
