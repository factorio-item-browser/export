<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Entity\Dump;

/**
 * The ingredient written to the dumped data.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class Ingredient
{
    /**
     * The type of the ingredient.
     * @var string
     */
    protected $type = '';

    /**
     * The name of the ingredient.
     * @var string
     */
    protected $name = '';

    /**
     * The amount needed of the ingredient.
     * @var float
     */
    protected $amount = 1.;

    /**
     * Sets the type of the ingredient.
     * @param string $type
     * @return $this
     */
    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Returns the type of the ingredient.
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Sets the name of the ingredient.
     * @param string $name
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Returns the name of the ingredient.
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Sets the amount needed of the ingredient.
     * @param float $amount
     * @return $this
     */
    public function setAmount(float $amount): self
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * Returns the amount needed of the ingredient.
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount;
    }
}
