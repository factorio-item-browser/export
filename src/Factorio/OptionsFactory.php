<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Factorio;

use BluePsyduck\Common\Data\DataContainer;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * The factory of the options class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class OptionsFactory implements FactoryInterface
{
    /**
     * Creates the options.
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return Options
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');
        $factorioConfig = new DataContainer($config['factorio']);

        $options = new Options();
        $options->setNumberOfAttempts($factorioConfig->getInteger('numberOfAttempts', 1))
                ->setFactorioDirectory($factorioConfig->getString('factorioDirectory'))
                ->setInstancesDirectory($factorioConfig->getString('instancesDirectory'));
        return $options;
    }
}