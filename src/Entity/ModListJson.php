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
    /**
     * The mod list.
     * @var array<Mod>|Mod[]
     */
    protected array $mods;

    /**
     * Sets the mods list.
     * @param array<Mod>|Mod[] $mods
     * @return $this
     */
    public function setMods(array $mods): self
    {
        $this->mods = $mods;
        return $this;
    }

    /**
     * Adds a mod to the list.
     * @param Mod $mod
     * @return $this
     */
    public function addMod(Mod $mod): self
    {
        $this->mods[] = $mod;
        return $this;
    }

    /**
     * Returns the mod list.
     * @return array<Mod>|Mod[]
     */
    public function getMods(): array
    {
        return $this->mods;
    }
}
