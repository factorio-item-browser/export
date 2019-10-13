<?php

declare(strict_types=1);

/**
 * The main configuration file building up the whole config array.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */

use Zend\ConfigAggregator\ArrayProvider;
use Zend\ConfigAggregator\ConfigAggregator;
use Zend\ConfigAggregator\PhpFileProvider;

$cacheConfig = [
    'config_cache_path' => 'data/cache/config-cache.php',
];

$aggregator = new ConfigAggregator([
    // Include cache configuration
    new ArrayProvider($cacheConfig),

    BluePsyduck\FactorioModPortalClient\ConfigProvider::class,
    FactorioItemBrowser\ExportData\ConfigProvider::class,
    FactorioItemBrowser\ExportQueue\Client\ConfigProvider::class,
    Zend\I18n\ConfigProvider::class,

    // Load application config in a pre-defined order in such a way that local settings
    // overwrite global settings. (Loaded as first to last):
    //   - `*.global.php`
    //   - `[FIB_ENV]/*.local.php`
    new PhpFileProvider(
        realpath(__DIR__) . sprintf('/autoload/{*.global.php,%s/*.local.php}', getenv('FIB_ENV') ?: 'production')
    ),
], $cacheConfig['config_cache_path']);

return $aggregator->getMergedConfig();
