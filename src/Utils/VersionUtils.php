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
    protected const VERSION_PART_COUNT = 3;

    /**
     * Normalizes the specified version.
     * @param string $version
     * @return string
     */
    public static function normalize(string $version): string
    {
        return implode('.', self::splitVersion($version));
    }

    /**
     * Compares the specified versions.
     * @param string $leftVersion
     * @param string $rightVersion
     * @return int 1 if the left version is greater, -1 if the right one is greater, or 0 if they are equal.
     */
    public static function compare(string $leftVersion, string $rightVersion): int
    {
        $leftParts = self::splitVersion($leftVersion);
        $rightParts = self::splitVersion($rightVersion);

        $result = 0;
        for ($i = 0; $i < self::VERSION_PART_COUNT && $result === 0; ++$i) {
            $result = $leftParts[$i] <=> $rightParts[$i];
        }
        return $result;
    }

    /**
     * Returns whether the specified version fulfills the factorio version.
     * @param string $currentFactorioVersion
     * @param string $requiredVersion
     * @return bool
     */
    public static function hasFactorioVersion(string $currentFactorioVersion, string $requiredVersion): bool
    {
        $requiredParts = self::splitVersion($requiredVersion);
        $required = [$requiredParts[0], $requiredParts[1]];

        $factorioParts = self::splitVersion($currentFactorioVersion);
        $factorio = [$factorioParts[0], $factorioParts[1]];

        return ($required === $factorio) || ($required === [0, 18] && $factorio === [1, 0]);
    }

    /**
     * Splits the specified version into an array of three integer values. Missing values are filled with 0.
     * @param string $version
     * @return array|int[]
     */
    protected static function splitVersion(string $version): array
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
    public static function getGreater(string $leftVersion, string $rightVersion): string
    {
        return self::normalize(self::compare($leftVersion, $rightVersion) > 0 ? $leftVersion : $rightVersion);
    }
}
