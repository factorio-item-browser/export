<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\OutputProcessor;

use FactorioItemBrowser\Export\Exception\DumpModNotLoadedException;
use FactorioItemBrowser\ExportData\Entity\Mod;
use FactorioItemBrowser\ExportData\ExportData;

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

    public function processLine(string $outputLine, ExportData $exportData): void
    {
        if (preg_match(self::REGEX_CHECKSUM, $outputLine, $match) > 0) {
            $mod = new Mod();
            $mod->name = $match[1];

            $exportData->getMods()->add($mod);
        }
    }

    public function processExitCode(int $exitCode, ExportData $exportData): void
    {
        /** @var array<Mod> $mods */
        $mods = iterator_to_array($exportData->getMods());
        $lastMod = array_pop($mods);
        if ($lastMod === null || $lastMod->name !== self::MOD_NAME_DUMP) {
            throw new DumpModNotLoadedException();
        }
        $exportData->getMods()->remove($lastMod); // Remove the dump mod from the export data.
    }
}
