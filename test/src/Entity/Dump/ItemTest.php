<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Entity\Dump;

use FactorioItemBrowser\Export\Entity\Dump\Item;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the Item class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Entity\Dump\Item
 */
class ItemTest extends TestCase
{
    /**
     * Tests the constructing.
     * @coversNothing
     */
    public function testConstruct(): void
    {
        $entity = new Item();

        $this->assertSame('', $entity->getName());
        $this->assertNull($entity->getLocalisedName());
        $this->assertNull($entity->getLocalisedDescription());
        $this->assertNull($entity->getLocalisedEntityName());
        $this->assertNull($entity->getLocalisedEntityDescription());
    }

    /**
     * Tests the setting and getting the name.
     * @covers ::getName
     * @covers ::setName
     */
    public function testSetAndGetName(): void
    {
        $name = 'abc';
        $entity = new Item();

        $this->assertSame($entity, $entity->setName($name));
        $this->assertSame($name, $entity->getName());
    }

    /**
     * Tests the setting and getting the localised name.
     * @covers ::getLocalisedName
     * @covers ::setLocalisedName
     */
    public function testSetAndGetLocalisedName(): void
    {
        $localisedName = ['abc'];
        $entity = new Item();

        $this->assertSame($entity, $entity->setLocalisedName($localisedName));
        $this->assertSame($localisedName, $entity->getLocalisedName());
    }

    /**
     * Tests the setting and getting the localised description.
     * @covers ::getLocalisedDescription
     * @covers ::setLocalisedDescription
     */
    public function testSetAndGetLocalisedDescription(): void
    {
        $localisedDescription = ['abc'];
        $entity = new Item();

        $this->assertSame($entity, $entity->setLocalisedDescription($localisedDescription));
        $this->assertSame($localisedDescription, $entity->getLocalisedDescription());
    }

    /**
     * Tests the setting and getting the localised entity name.
     * @covers ::getLocalisedEntityName
     * @covers ::setLocalisedEntityName
     */
    public function testSetAndGetLocalisedEntityName(): void
    {
        $localisedEntityName = ['abc'];
        $entity = new Item();

        $this->assertSame($entity, $entity->setLocalisedEntityName($localisedEntityName));
        $this->assertSame($localisedEntityName, $entity->getLocalisedEntityName());
    }

    /**
     * Tests the setting and getting the localised entity description.
     * @covers ::getLocalisedEntityDescription
     * @covers ::setLocalisedEntityDescription
     */
    public function testSetAndGetLocalisedEntityDescription(): void
    {
        $localisedEntityDescription = ['abc'];
        $entity = new Item();

        $this->assertSame($entity, $entity->setLocalisedEntityDescription($localisedEntityDescription));
        $this->assertSame($localisedEntityDescription, $entity->getLocalisedEntityDescription());
    }
}
