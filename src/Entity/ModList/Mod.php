<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Entity\ModList;

/**
 * The class representing a mod of the mod-list.json file.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class Mod
{
    /**
     * The name of the mod.
     * @var string
     */
    protected string $name = '';

    /**
     * Whether the mod is enabled.
     * @var bool
     */
    protected bool $enabled = false;

    /**
     * Sets the name of the mod.
     * @param string $name
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Returns the name of the mod.
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Sets whether the mod is enabled.
     * @param bool $enabled
     * @return $this
     */
    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;
        return $this;
    }

    /**
     * Returns whether the mod is enabled.
     * @return bool
     */
    public function getEnabled(): bool
    {
        return $this->enabled;
    }
}
