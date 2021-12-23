<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Entity\Dump;

use JMS\Serializer\Annotation\Type;

/**
 * The recipe written to the dumped data.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class Recipe
{
    public string $name = '';
    #[Type('raw')]
    public mixed $localisedName = null;
    #[Type('raw')]
    public mixed $localisedDescription = null;
    public float $craftingTime = 0.;
    public string $craftingCategory = '';
    /** @var array<Ingredient> */
    #[Type('array<' . Ingredient::class . '>')]
    public array $ingredients = [];
    /** @var array<Product> */
    #[Type('array<' . Product::class . '>')]
    public $products = [];
}
