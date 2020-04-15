<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Entity\Dump;

/**
 * The item written to the dumped data.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class Item
{
    /**
     * The name of the item.
     * @var string
     */
    protected $name = '';

    /**
     * The localised name of the item.
     * @var mixed
     */
    protected $localisedName;

    /**
     * The localised description of the item.
     * @var mixed
     */
    protected $localisedDescription;

    /**
     * The localised entity name of the item.
     * @var mixed
     */
    protected $localisedEntityName;

    /**
     * The localised entity description of the item.
     * @var mixed
     */
    protected $localisedEntityDescription;

    /**
     * Sets the name of the item.
     * @param string $name
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Returns the name of the item.
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Sets the localised name of the item.
     * @param mixed $localisedName
     * @return $this
     */
    public function setLocalisedName($localisedName): self
    {
        $this->localisedName = $localisedName;
        return $this;
    }

    /**
     * Returns the localised name of the item.
     * @return mixed
     */
    public function getLocalisedName()
    {
        return $this->localisedName;
    }

    /**
     * Sets the localised description of the item.
     * @param mixed $localisedDescription
     * @return $this
     */
    public function setLocalisedDescription($localisedDescription): self
    {
        $this->localisedDescription = $localisedDescription;
        return $this;
    }

    /**
     * Returns the localised description of the item.
     * @return mixed
     */
    public function getLocalisedDescription()
    {
        return $this->localisedDescription;
    }

    /**
     * Sets the localised entity name of the item.
     * @param mixed $localisedEntityName
     * @return $this
     */
    public function setLocalisedEntityName($localisedEntityName): self
    {
        $this->localisedEntityName = $localisedEntityName;
        return $this;
    }

    /**
     * Returns the localised entity name of the item.
     * @return mixed
     */
    public function getLocalisedEntityName()
    {
        return $this->localisedEntityName;
    }

    /**
     * Sets the localised entity description of the item.
     * @param mixed $localisedEntityDescription
     * @return $this
     */
    public function setLocalisedEntityDescription($localisedEntityDescription): self
    {
        $this->localisedEntityDescription = $localisedEntityDescription;
        return $this;
    }

    /**
     * Returns the localised entity description of the item.
     * @return mixed
     */
    public function getLocalisedEntityDescription()
    {
        return $this->localisedEntityDescription;
    }
}
