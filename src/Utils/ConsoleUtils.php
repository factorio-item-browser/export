<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Utils;

/**
 * The utils class for printing some values to the console.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ConsoleUtils
{
    /**
     * Formats the mod name for the console.
     * @param string $modName
     * @param string $suffix
     * @return string
     */
    public static function formatModName(string $modName, string $suffix = ''): string
    {
        return str_pad($modName, 64, ' ', STR_PAD_LEFT) . $suffix;
    }

    /**
     * Formats the version for the console.
     * @param string $version
     * @param bool $padLeft
     * @return string
     */
    public static function formatVersion(string $version, bool $padLeft = false): string
    {
        $version = $version === '' ? '' : VersionUtils::normalize($version);
        return str_pad($version, 10, ' ', $padLeft ? STR_PAD_LEFT : STR_PAD_RIGHT);
    }
}
