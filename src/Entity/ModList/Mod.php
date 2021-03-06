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
    public string $name = '';
    public bool $isEnabled = false;
}
