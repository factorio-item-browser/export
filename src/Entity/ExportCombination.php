<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Entity;

use FactorioItemBrowser\ExportData\Entity\Mod\Combination;

/**
 * The extension of the combination entity to hold more data about the export.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ExportCombination extends Combination
{
    /**
     * The parent combinations.
     * @var Combination[]
     */
    protected $parentCombinations = [];

    /**
     * Whether the combination data has already been reduced.
     * @var bool
     */
    protected $isReduced = false;

    /**
     * @param Combination[] $parentCombinations
     * @return $this
     */
    public function setParentCombinations(array $parentCombinations)
    {
        $this->parentCombinations = array_values(array_filter($parentCombinations, function ($combination): bool {
            return $combination instanceof Combination;
        }));
        return $this;
    }

    /**
     * Returns the parent combinations.
     * @return Combination[]
     */
    public function getParentCombinations(): array
    {
        return $this->parentCombinations;
    }

    /**
     * Sets whether the combination data has already been reduced.
     * @param bool $isReduced
     * @return $this
     */
    public function setIsReduced(bool $isReduced)
    {
        $this->isReduced = $isReduced;
        return $this;
    }

    /**
     * Returns whether the combination data has already been reduced.
     * @return bool
     */
    public function getIsReduced(): bool
    {
        return $this->isReduced;
    }
}
