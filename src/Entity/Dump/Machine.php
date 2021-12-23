<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Entity\Dump;

use JMS\Serializer\Annotation\Type;

/**
 * The machine written to the dumped data.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class Machine
{
    public string $name = '';
    #[Type('raw')]
    public mixed $localisedName = null;
    #[Type('raw')]
    public mixed $localisedDescription = null;
    /** @var array<string> */
    #[Type('array<string>')]
    public array $craftingCategories = [];
    public float $craftingSpeed = 1.;
    public int $itemSlots = 0;
    public int $fluidInputSlots = 0;
    public int $fluidOutputSlots = 0;
    public int $moduleSlots = 0;
    public float $energyUsage = 0.;
}
