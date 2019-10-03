<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Entity;

use FactorioItemBrowser\Export\Entity\InfoJson;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the InfoJson class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Entity\InfoJson
 */
class InfoJsonTest extends TestCase
{
    /**
     * Tests the constructing.
     * @coversNothing
     */
    public function testConstruct(): void
    {
        $entity = new InfoJson();

        $this->assertSame('', $entity->getName());
        $this->assertSame('', $entity->getTitle());
        $this->assertSame('', $entity->getDescription());
        $this->assertSame('', $entity->getVersion());
        $this->assertSame('', $entity->getFactorioVersion());
        $this->assertSame('', $entity->getAuthor());
        $this->assertSame('', $entity->getContact());
        $this->assertSame('', $entity->getHomepage());
        $this->assertSame([], $entity->getDependencies());
    }

    /**
     * Tests the setting and getting the name.
     * @covers ::getName
     * @covers ::setName
     */
    public function testSetAndGetName(): void
    {
        $name = 'abc';
        $entity = new InfoJson();

        $this->assertSame($entity, $entity->setName($name));
        $this->assertSame($name, $entity->getName());
    }

    /**
     * Tests the setting and getting the title.
     * @covers ::getTitle
     * @covers ::setTitle
     */
    public function testSetAndGetTitle(): void
    {
        $title = 'abc';
        $entity = new InfoJson();

        $this->assertSame($entity, $entity->setTitle($title));
        $this->assertSame($title, $entity->getTitle());
    }

    /**
     * Tests the setting and getting the description.
     * @covers ::getDescription
     * @covers ::setDescription
     */
    public function testSetAndGetDescription(): void
    {
        $description = 'abc';
        $entity = new InfoJson();

        $this->assertSame($entity, $entity->setDescription($description));
        $this->assertSame($description, $entity->getDescription());
    }

    /**
     * Tests the setting and getting the version.
     * @covers ::getVersion
     * @covers ::setVersion
     */
    public function testSetAndGetVersion(): void
    {
        $version = '1.2.3';
        $entity = new InfoJson();

        $this->assertSame($entity, $entity->setVersion($version));
        $this->assertSame($version, $entity->getVersion());
    }

    /**
     * Tests the setting and getting the factorio version.
     * @covers ::getFactorioVersion
     * @covers ::setFactorioVersion
     */
    public function testSetAndGetFactorioVersion(): void
    {
        $factorioVersion = '0.1.2';
        $entity = new InfoJson();

        $this->assertSame($entity, $entity->setFactorioVersion($factorioVersion));
        $this->assertSame($factorioVersion, $entity->getFactorioVersion());
    }

    /**
     * Tests the setting and getting the author.
     * @covers ::getAuthor
     * @covers ::setAuthor
     */
    public function testSetAndGetAuthor(): void
    {
        $author = 'abc';
        $entity = new InfoJson();

        $this->assertSame($entity, $entity->setAuthor($author));
        $this->assertSame($author, $entity->getAuthor());
    }

    /**
     * Tests the setting and getting the contact.
     * @covers ::getContact
     * @covers ::setContact
     */
    public function testSetAndGetContact(): void
    {
        $contact = 'abc';
        $entity = new InfoJson();

        $this->assertSame($entity, $entity->setContact($contact));
        $this->assertSame($contact, $entity->getContact());
    }

    /**
     * Tests the setting and getting the homepage.
     * @covers ::getHomepage
     * @covers ::setHomepage
     */
    public function testSetAndGetHomepage(): void
    {
        $homepage = 'abc';
        $entity = new InfoJson();

        $this->assertSame($entity, $entity->setHomepage($homepage));
        $this->assertSame($homepage, $entity->getHomepage());
    }

    /**
     * Tests the setting and getting the dependencies.
     * @covers ::getDependencies
     * @covers ::setDependencies
     */
    public function testSetAndGetDependencies(): void
    {
        $dependencies = ['abc', 'def'];
        $entity = new InfoJson();

        $this->assertSame($entity, $entity->setDependencies($dependencies));
        $this->assertSame($dependencies, $entity->getDependencies());
    }
}
