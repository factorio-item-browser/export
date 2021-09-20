<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Process;

use Symfony\Component\Process\Process;

/**
 * The process to run another command.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class CommandProcess extends Process
{
    /**
     * @param string $commandName
     * @param array<string> $additionalArguments
     */
    public function __construct(string $commandName, array $additionalArguments = [])
    {
        parent::__construct([
            $_SERVER['_'] ?? 'php',
            $_SERVER['SCRIPT_FILENAME'],
            $commandName,
            ...$additionalArguments,
        ]);

        $this->setTimeout(null);
    }
}
