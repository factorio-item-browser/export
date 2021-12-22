<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Output;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The factory for the console output instance.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ConsoleOutputFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array<mixed>|null $options
     * @return ConsoleOutput
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ConsoleOutput
    {
        return new ConsoleOutput(OutputInterface::VERBOSITY_NORMAL, true);
    }
}
