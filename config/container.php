<?php

declare(strict_types=1);

/**
 * The file initializing the container with the dependencies.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */

use BluePsyduck\LaminasAutoWireFactory\AutoWireFactory;
use Laminas\ConfigAggregator\ConfigAggregator;
use Laminas\ServiceManager\Config;
use Laminas\ServiceManager\ServiceManager;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

$config = require(__DIR__ . '/config.php');

$container = new ServiceManager();
(new Config($config['dependencies']))->configureServiceManager($container);

if ($config[ConfigAggregator::ENABLE_CACHE] ?? false) {
    AutoWireFactory::setCacheFile(__DIR__ . '/../data/cache/autowire-factory-cache.php');
}

$container->setService('config', $config);
$container->setService(OutputInterface::class, new ConsoleOutput(ConsoleOutput::VERBOSITY_NORMAL, true));

return $container;
