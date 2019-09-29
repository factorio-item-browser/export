<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Entity\Dump;

/**
 * The dump data from the data stage.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class DataStage
{
    /**
     * The icons of the dump.
     * @var array|Icon[]
     */
    protected $icons = [];

    /**
     * Sets the icons of the dump.
     * @param array|Icon[] $icons
     * @return $this
     */
    public function setIcons(array $icons): self
    {
        $this->icons = $icons;
        return $this;
    }

    /**
     * Returns the icons of the dump.
     * @return array|Icon[]
     */
    public function getIcons(): array
    {
        return $this->icons;
    }
}
