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
use Zend\ServiceManager\Factory\InvokableFactory;

return [
    'dependencies' => [
        'factories'  => [
            ExportDataService::class => ExportData\ExportDataServiceFactory::class,

            Command\HelloWorldCommand::class => InvokableFactory::class,
            Command\Show\ShowMissingCommand::class => Command\Show\ShowMissingCommandFactory::class,
        ],
    ]
];
