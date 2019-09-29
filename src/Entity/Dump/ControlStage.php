<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Entity\Dump;

/**
 * The dump data from the control stage.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ControlStage
{
    /**
     * The items of the dump.
     * @var array|Item[]
     */
    protected $items = [];

    /**
     * The fluids of the dump.
     * @var array|Fluid[]
     */
    protected $fluids = [];

    /**
     * The machines of the dump.
     * @var array|Machine[]
     */
    protected $machines = [];

    /**
     * The normal recipes of the dump.
     * @var array|Recipe[]
     */
    protected $normalRecipes = [];

    /**
     * The expensive recipes of the dump.
     * @var array|Recipe[]
     */
    protected $expensiveRecipes = [];

    /**
     * Sets the items of the dump.
     * @param array|Item[] $items
     * @return $this
     */
    public function setItems(array $items): self
    {
        $this->items = $items;
        return $this;
    }

    /**
     * Returns the items of the dump.
     * @return array|Item[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * Sets the fluids of the dump.
     * @param array|Fluid[] $fluids
     * @return $this
     */
    public function setFluids(array $fluids): self
    {
        $this->fluids = $fluids;
        return $this;
    }

    /**
     * Returns the fluids of the dump.
     * @return array|Fluid[]
     */
    public function getFluids(): array
    {
        return $this->fluids;
    }

    /**
     * Sets the machines of the dump.
     * @param array|Machine[] $machines
     * @return $this
     */
    public function setMachines(array $machines): self
    {
        $this->machines = $machines;
        return $this;
    }

    /**
     * Returns the machines of the dump.
     * @return array|Machine[]
     */
    public function getMachines(): array
    {
        return $this->machines;
    }

    /**
     * Sets the normal recipes of the dump.
     * @param array|Recipe[] $normalRecipes
     * @return $this
     */
    public function setNormalRecipes(array $normalRecipes): self
    {
        $this->normalRecipes = $normalRecipes;
        return $this;
    }

    /**
     * Returns the normal recipes of the dump.
     * @return array|Recipe[]
     */
    public function getNormalRecipes(): array
    {
        return $this->normalRecipes;
    }

    /**
     * Sets the expensive recipes of the dump.
     * @param array|Recipe[] $expensiveRecipes
     * @return $this
     */
    public function setExpensiveRecipes(array $expensiveRecipes): self
    {
        $this->expensiveRecipes = $expensiveRecipes;
        return $this;
    }

    /**
     * Returns the expensive recipes of the dump.
     * @return array|Recipe[]
     */
    public function getExpensiveRecipes(): array
    {
        return $this->expensiveRecipes;
    }
}
