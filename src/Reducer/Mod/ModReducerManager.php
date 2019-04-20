<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Reducer\Mod;

use FactorioItemBrowser\Export\Exception\ReducerException;
use FactorioItemBrowser\ExportData\Entity\Mod;

/**
 * The manager of the mod reducer classes.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ModReducerManager
{
    /**
     * The reducers to use.
     * @var array|ModReducerInterface[]
     */
    protected $reducers;

    /**
     * Initializes the reducer manager.
     * @param array|ModReducerInterface[] $reducers
     */
    public function __construct(array $reducers)
    {
        $this->reducers = $reducers;
    }

    /**
     * Reduces the specified combination against its parents.
     * @param Mod $mod
     * @return Mod
     * @throws ReducerException
     */
    public function reduce(Mod $mod): Mod
    {
        $result = clone($mod);
        $this->reduceMod($result);
        return $result;
    }

    /**
     * Reduces the mod.
     * @param Mod $mod
     * @throws ReducerException
     */
    protected function reduceMod(Mod $mod): void
    {
        foreach ($this->reducers as $reducer) {
            $reducer->reduce($mod);
        }
    }
}
