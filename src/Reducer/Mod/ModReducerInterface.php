<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Reducer\Mod;

use FactorioItemBrowser\Export\Exception\ReducerException;
use FactorioItemBrowser\ExportData\Entity\Mod;

/**
 * The interface for the mod reducer classes.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
interface ModReducerInterface
{
    /**
     * Reduces the mod.
     * @param Mod $mod
     * @throws ReducerException
     */
    public function reduce(Mod $mod): void;
}
