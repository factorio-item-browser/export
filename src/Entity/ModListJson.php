<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Entity;

use FactorioItemBrowser\Export\Entity\ModList\Mod;

/**
 * The entity representing the mod-list.json file.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ModListJson
{
    /** @var array<Mod> */
    public array $mods;
}
