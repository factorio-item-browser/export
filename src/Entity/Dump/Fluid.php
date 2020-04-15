<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Entity\Dump;

/**
 * The fluid written to the dumped data.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class Fluid
{
    /**
     * The name of the fluid.
     * @var string
     */
    protected $name = '';

    /**
     * The localised name of the fluid.
     * @var mixed
     */
    protected $localisedName;

    /**
     * The localised description of the fluid.
     * @var mixed
     */
    protected $localisedDescription;

    /**
     * Sets the name of the fluid.
     * @param string $name
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Returns the name of the fluid.
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Sets the localised name of the fluid.
     * @param mixed $localisedName
     * @return $this
     */
    public function setLocalisedName($localisedName): self
    {
        $this->localisedName = $localisedName;
        return $this;
    }

    /**
     * Returns the localised name of the fluid.
     * @return mixed
     */
    public function getLocalisedName()
    {
        return $this->localisedName;
    }

    /**
     * Sets the localised description of the fluid.
     * @param mixed $localisedDescription
     * @return $this
     */
    public function setLocalisedDescription($localisedDescription): self
    {
        $this->localisedDescription = $localisedDescription;
        return $this;
    }

    /**
     * Returns the localised description of the fluid.
     * @return mixed
     */
    public function getLocalisedDescription()
    {
        return $this->localisedDescription;
    }
}
