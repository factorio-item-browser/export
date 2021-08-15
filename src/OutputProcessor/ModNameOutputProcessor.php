<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\OutputProcessor;

use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\Exception\DumpModNotLoadedException;

/**
 * The processor for reading the mod names and their exact order.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ModNameOutputProcessor implements OutputProcessorInterface
{
    private const REGEX_CHECKSUM = '#^\s*[0-9.]+ Checksum of (.*): \d+$#m';
    private const MOD_NAME_DUMP = 'Dump';

    public function processLine(string $outputLine, Dump $dump): void
    {
        if (preg_match(self::REGEX_CHECKSUM, $outputLine, $match) > 0) {
            $dump->modNames[] = $match[1];
        }
    }

    public function processExitCode(int $exitCode, Dump $dump): void
    {
        $lastModName = array_pop($dump->modNames);
        if ($lastModName !== self::MOD_NAME_DUMP) {
            throw new DumpModNotLoadedException();
        }
    }
}
