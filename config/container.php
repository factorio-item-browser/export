<?php

declare(strict_types=1);

/**
 * The file initializing the container with the dependencies.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */

use Zend\ServiceManager\Config;
use Zend\ServiceManager\ServiceManager;

$config = require(__DIR__ . '/config.php');

$container = new ServiceManager();
(new Config($config['dependencies']))->configureServiceManager($container);

$container->setService('config', $config);
return $container;
