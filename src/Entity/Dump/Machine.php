<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Entity\Dump;

/**
 * The machine written to the dumped data.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class Machine
{
    /**
     * The name of the machine.
     * @var string
     */
    protected $name = '';

    /**
     * The localised name of the machine.
     * @var mixed
     */
    protected $localisedName;

    /**
     * The localised description of the machine.
     * @var mixed
     */
    protected $localisedDescription;

    /**
     * The crafting categories supported by the machine.
     * @var array|string[]
     */
    protected $craftingCategories = [];

    /**
     * The crafting speed of the machine.
     * @var float
     */
    protected $craftingSpeed = 1.;

    /**
     * The number of item slots of the machine.
     * @var int
     */
    protected $itemSlots = 0;

    /**
     * The number of fluid input slots of the machine.
     * @var int
     */
    protected $fluidInputSlots = 0;

    /**
     * The number of fluid output slots of the machine.
     * @var int
     */
    protected $fluidOutputSlots = 0;

    /**
     * The number of module slots of the machine.
     * @var int
     */
    protected $moduleSlots = 0;

    /**
     * The energy usage of the machine.
     * @var float
     */
    protected $energyUsage = 0.;

    /**
     * Sets the name of the machine.
     * @param string $name
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Returns the name of the machine.
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Sets the localised name of the machine.
     * @param mixed $localisedName
     * @return $this
     */
    public function setLocalisedName($localisedName): self
    {
        $this->localisedName = $localisedName;
        return $this;
    }

    /**
     * Returns the localised name of the machine.
     * @return mixed
     */
    public function getLocalisedName()
    {
        return $this->localisedName;
    }

    /**
     * Sets the localised description of the machine.
     * @param mixed $localisedDescription
     * @return $this
     */
    public function setLocalisedDescription($localisedDescription): self
    {
        $this->localisedDescription = $localisedDescription;
        return $this;
    }

    /**
     * Returns the localised description of the machine.
     * @return mixed
     */
    public function getLocalisedDescription()
    {
        return $this->localisedDescription;
    }

    /**
     * Sets the crafting categories supported by the machine.
     * @param array|string[] $craftingCategories
     * @return $this
     */
    public function setCraftingCategories(array $craftingCategories): self
    {
        $this->craftingCategories = $craftingCategories;
        return $this;
    }

    /**
     * Returns the crafting categories supported by the machine.
     * @return array|string[]
     */
    public function getCraftingCategories(): array
    {
        return $this->craftingCategories;
    }

    /**
     * Sets the crafting speed of the machine.
     * @param float $craftingSpeed
     * @return $this
     */
    public function setCraftingSpeed(float $craftingSpeed): self
    {
        $this->craftingSpeed = $craftingSpeed;
        return $this;
    }

    /**
     * Returns the crafting speed of the machine.
     * @return float
     */
    public function getCraftingSpeed(): float
    {
        return $this->craftingSpeed;
    }

    /**
     * Sets the number of item slots of the machine.
     * @param int $itemSlots
     * @return $this
     */
    public function setItemSlots(int $itemSlots): self
    {
        $this->itemSlots = $itemSlots;
        return $this;
    }

    /**
     * Returns the number of item slots of the machine.
     * @return int
     */
    public function getItemSlots(): int
    {
        return $this->itemSlots;
    }

    /**
     * Sets the number of fluid input slots of the machine.
     * @param int $fluidInputSlots
     * @return $this
     */
    public function setFluidInputSlots(int $fluidInputSlots): self
    {
        $this->fluidInputSlots = $fluidInputSlots;
        return $this;
    }

    /**
     * Returns the number of fluid input slots of the machine.
     * @return int
     */
    public function getFluidInputSlots(): int
    {
        return $this->fluidInputSlots;
    }

    /**
     * Sets the number of fluid output slots of the machine.
     * @param int $fluidOutputSlots
     * @return $this
     */
    public function setFluidOutputSlots(int $fluidOutputSlots): self
    {
        $this->fluidOutputSlots = $fluidOutputSlots;
        return $this;
    }

    /**
     * Returns the number of fluid output slots of the machine.
     * @return int
     */
    public function getFluidOutputSlots(): int
    {
        return $this->fluidOutputSlots;
    }

    /**
     * Sets the number of module slots of the machine.
     * @param int $moduleSlots
     * @return $this
     */
    public function setModuleSlots(int $moduleSlots): self
    {
        $this->moduleSlots = $moduleSlots;
        return $this;
    }

    /**
     * Returns the number of module slots of the machine.
     * @return int
     */
    public function getModuleSlots(): int
    {
        return $this->moduleSlots;
    }

    /**
     * Sets the energy usage of the machine.
     * @param float $energyUsage
     * @return $this
     */
    public function setEnergyUsage(float $energyUsage): self
    {
        $this->energyUsage = $energyUsage;
        return $this;
    }

    /**
     * Returns the energy usage of the machine.
     * @return float
     */
    public function getEnergyUsage(): float
    {
        return $this->energyUsage;
    }
}
