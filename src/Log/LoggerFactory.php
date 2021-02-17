<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Log;

use FactorioItemBrowser\Export\Constant\ConfigKey;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

/**
 * The factory for the logger.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class LoggerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array<mixed>|null $options
     * @return LoggerInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): LoggerInterface
    {
        /** @var array<mixed> $config */
        $config = $container->get('config');
        $logDirectory = $config[ConfigKey::MAIN][ConfigKey::DIRECTORIES][ConfigKey::DIRECTORY_LOGS];

        $logger = new Logger('cli');
        $logger->pushHandler(new StreamHandler(sprintf("%s/cli_%s.log", $logDirectory, date('Y-m-d'))));
        return $logger;
    }
}
