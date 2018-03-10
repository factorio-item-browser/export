<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command;

use FactorioItemBrowser\Export\I18n\Translator;
use Interop\Container\ContainerInterface;

/**
 * The factory of the test command.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class TestCommandFactory
{
    /**
     * Creates the test command.
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return TestCommand
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var Translator $translator */
        $translator = $container->get(Translator::class);

        return new TestCommand($translator);
    }
}