<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Entity\Dump;

/**
 * The product written to the dumped data.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class Product
{
    public string $type = '';
    public string $name = '';
    public float $amountMin = 1.;
    public float $amountMax = 1.;
    public float $probability = 1.;
}
