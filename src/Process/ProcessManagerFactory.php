<?php

namespace FactorioItemBrowser\Export\Process;

use BluePsyduck\SymfonyProcessManager\ProcessManager;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * The factory of the process manager.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ProcessManagerFactory implements FactoryInterface
{
    /**
     * Creates the process manager.
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return ProcessManager
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');

        return new ProcessManager(
            $config['process-manager']['process-count'],
            $config['process-manager']['poll-interval']
        );
    }
}
