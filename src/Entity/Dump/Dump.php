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
    /**
     * The mod names in the order of how they were loaded.
     * @var array|string[]
     */
    protected $modNames = [];

    /**
     * The data of the data stage.
     * @var DataStage
     */
    protected $dataStage;

    /**
     * The data of the control stage.
     * @var ControlStage
     */
    protected $controlStage;

    /**
     * Initializes the entity.
     */
    public function __construct()
    {
        $this->dataStage = new DataStage();
        $this->controlStage = new ControlStage();
    }

    /**
     * Sets the mod names in the order of how they were loaded.
     * @param array|string[] $modNames
     * @return $this
     */
    public function setModNames(array $modNames): self
    {
        $this->modNames = $modNames;
        return $this;
    }

    /**
     * Returns the mod names in the order of how they were loaded.
     * @return array|string[]
     */
    public function getModNames(): array
    {
        return $this->modNames;
    }

    /**
     * Sets the data of the data stage.
     * @param DataStage $dataStage
     * @return $this
     */
    public function setDataStage(DataStage $dataStage): self
    {
        $this->dataStage = $dataStage;
        return $this;
    }

    /**
     * Returns the data of the data stage.
     * @return DataStage
     */
    public function getDataStage(): DataStage
    {
        return $this->dataStage;
    }

    /**
     * Sets the data of the control stage.
     * @param ControlStage $controlStage
     * @return $this
     */
    public function setControlStage(ControlStage $controlStage): self
    {
        $this->controlStage = $controlStage;
        return $this;
    }

    /**
     * Returns the data of the control stage.
     * @return ControlStage
     */
    public function getControlStage(): ControlStage
    {
        return $this->controlStage;
    }
}
