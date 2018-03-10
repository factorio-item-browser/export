<?php

namespace FactorioItemBrowser\Export\Utils;

/**
 * Utils class for managing versions.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class VersionUtils
{
    /**
     * The number of parts a version consists of.
     */
    private const VERSION_PART_COUNT = 3;

    /**
     * Normalizes the specified version.
     * @param string $version
     * @return string
     */
    static public function normalize($version)
    {
        return implode('.', self::splitVersion($version));
    }

    /**
     * Compares the specified versions.
     * @param string $leftVersion
     * @param string $rightVersion
     * @return int 1 if the left version is greater, -1 if the right one is greater, or 0 if they are equal.
     */
    static public function compare($leftVersion, $rightVersion)
    {
        $leftParts = self::splitVersion($leftVersion);
        $rightParts = self::splitVersion($rightVersion);

        $result = 0;
        while ($result === 0 && !empty($leftParts)) {
            $result = array_shift($leftParts) <=> array_shift($rightParts);
        }
        return $result;
    }

    /**
     * Splits the specified version into an array of three integer values. Missing values are filled with 0.
     * @param string $version
     * @return array|int[]
     */
    static protected function splitVersion($version)
    {
        $defaultParts = array_fill(0, self::VERSION_PART_COUNT, 0);
        $parts = array_map('intval', explode('.', $version));
        return array_slice(array_merge($parts, $defaultParts), 0, self::VERSION_PART_COUNT);
    }

    /**
     * Returns the greater of the two specified versions.
     * @param string $leftVersion
     * @param string $rightVersion
     * @return string
     */
    static public function getGreater($leftVersion, $rightVersion)
    {
        return self::compare($leftVersion, $rightVersion) > 0 ? $leftVersion : $rightVersion;
    }
}