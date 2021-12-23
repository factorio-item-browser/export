<?php

/**
 * The configuration of the flysystem component.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */

declare(strict_types=1);

namespace FactorioItemBrowser\Export;

return [
    'flysystem' => [
        'adaptors' => [ // sic!
            'upload' => [
                'type' => 'ftp',
                'options' => [
                    'host' => 'fib-ftp',
                    'username' => 'export',
                    'password' => 'export',
                    'root' => '/',
                ],
            ],
        ],
    ],
];
