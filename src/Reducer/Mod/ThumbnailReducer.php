<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Reducer\Mod;

use FactorioItemBrowser\Export\Exception\ReducerException;
use FactorioItemBrowser\ExportData\Entity\Icon;
use FactorioItemBrowser\ExportData\Entity\Mod;
use FactorioItemBrowser\ExportData\Registry\EntityRegistry;

/**
 * The reducer copying the thumbnail icon to the reduced registry.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ThumbnailReducer implements ModReducerInterface
{
    /**
     * The raw icon registry.
     * @var EntityRegistry
     */
    protected $rawIconRegistry;

    /**
     * The reduced icon registry.
     * @var EntityRegistry
     */
    protected $reducedIconRegistry;

    /**
     * Initializes the reducer.
     * @param EntityRegistry $rawIconRegistry
     * @param EntityRegistry $reducedIconRegistry
     */
    public function __construct(EntityRegistry $rawIconRegistry, EntityRegistry $reducedIconRegistry)
    {
        $this->rawIconRegistry = $rawIconRegistry;
        $this->reducedIconRegistry = $reducedIconRegistry;
    }

    /**
     * Reduces the mod.
     * @param Mod $mod
     * @throws ReducerException
     */
    public function reduce(Mod $mod): void
    {
        $thumbnailHash = $mod->getThumbnailHash();
        if ($thumbnailHash !== '') {
            $icon = $this->rawIconRegistry->get($thumbnailHash);
            if (!$icon instanceof Icon) {
                throw new ReducerException('Cannot find thumbnail with hash #' . $thumbnailHash);
            }
            $this->reducedIconRegistry->set($icon);
        }
    }
}
