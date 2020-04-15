<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Entity;

/**
 * The entity representing the info.json file of a mod.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class InfoJson
{
    /**
     * The name of the mod.
     * @var string
     */
    protected $name = '';

    /**
     * The (English) title of the mod.
     * @var string
     */
    protected $title = '';

    /**
     * The (English) description of the mod.
     * @var string
     */
    protected $description = '';

    /**
     * The version of the mod.
     * @var string
     */
    protected $version = '';

    /**
     * The Factorio version required by the mod.
     * @var string
     */
    protected $factorioVersion = '';

    /**
     * The author of the mod.
     * @var string
     */
    protected $author = '';

    /**
     * The contact of the mod.
     * @var string
     */
    protected $contact = '';

    /**
     * The homepage of the mod.
     * @var string
     */
    protected $homepage = '';

    /**
     * The dependencies of the mod.
     * @var array|string[]
     */
    protected $dependencies = [];

    /**
     * Sets the name of the mod.
     * @param string $name
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Returns the name of the mod.
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Sets the (English) title of the mod.
     * @param string $title
     * @return $this
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Returns the (English) title of the mod.
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Sets the (English) description of the mod.
     * @param string $description
     * @return $this
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Returns the (English) description of the mod.
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Sets the version of the mod.
     * @param string $version
     * @return $this
     */
    public function setVersion(string $version): self
    {
        $this->version = $version;
        return $this;
    }

    /**
     * Returns the version of the mod.
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * Sets the Factorio version required by the mod.
     * @param string $factorioVersion
     * @return $this
     */
    public function setFactorioVersion(string $factorioVersion): self
    {
        $this->factorioVersion = $factorioVersion;
        return $this;
    }

    /**
     * Returns the Factorio version required by the mod.
     * @return string
     */
    public function getFactorioVersion(): string
    {
        return $this->factorioVersion;
    }

    /**
     * Sets the author of the mod.
     * @param string $author
     * @return $this
     */
    public function setAuthor(string $author): self
    {
        $this->author = $author;
        return $this;
    }

    /**
     * Returns the author of the mod.
     * @return string
     */
    public function getAuthor(): string
    {
        return $this->author;
    }

    /**
     * Sets the contact of the mod.
     * @param string $contact
     * @return $this
     */
    public function setContact(string $contact): self
    {
        $this->contact = $contact;
        return $this;
    }

    /**
     * Returns the contact of the mod.
     * @return string
     */
    public function getContact(): string
    {
        return $this->contact;
    }

    /**
     * Sets the homepage of the mod.
     * @param string $homepage
     * @return $this
     */
    public function setHomepage(string $homepage): self
    {
        $this->homepage = $homepage;
        return $this;
    }

    /**
     * Returns the homepage of the mod.
     * @return string
     */
    public function getHomepage(): string
    {
        return $this->homepage;
    }

    /**
     * Sets the dependencies of the mod.
     * @param array|string[] $dependencies
     * @return $this
     */
    public function setDependencies(array $dependencies): self
    {
        $this->dependencies = $dependencies;
        return $this;
    }

    /**
     * Returns the dependencies of the mod.
     * @return array|string[]
     */
    public function getDependencies(): array
    {
        return $this->dependencies;
    }
}
