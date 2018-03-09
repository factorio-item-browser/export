<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command;

use Zend\Console\Adapter\AdapterInterface as Console;
use ZF\Console\Route;

/**
 * Hello World command for testing purposes.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class HelloWorldCommand
{
    public function __invoke(Route $route, Console $console)
    {
        $console->writeLine('Hello World!');
    }
}