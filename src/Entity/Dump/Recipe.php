<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Entity\Dump;

/**
 * The recipe written to the dumped data.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class Recipe
{
    public string $name = '';
    /** @var mixed */
    public $localisedName;
    /** @var mixed */
    public $localisedDescription;
    public float $craftingTime = 0.;
    public string $craftingCategory = '';
    /** @var array<Ingredient> */
    public array $ingredients = [];
    /** @var array<Product> */
    public $products = [];
}
