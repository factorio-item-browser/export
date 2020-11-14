<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Entity\Dump;

/**
 * The class representing the full dump.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class Dump
{
    /** @var array<string> */
    public array $modNames = [];
    /** @var array<Icon> */
    public array $icons = [];
    /** @var array<Item> */
    public array $items = [];
    /** @var array<Fluid> */
    public array $fluids = [];
    /** @var array<Machine> */
    public array $machines = [];
    /** @var array<Recipe> */
    public array $normalRecipes = [];
    /** @var array<Recipe> */
    public array $expensiveRecipes = [];
}
