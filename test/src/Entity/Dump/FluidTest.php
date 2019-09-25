<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Entity\Dump;

use FactorioItemBrowser\Export\Entity\Dump\Fluid;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the Fluid class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Entity\Dump\Fluid
 */
class FluidTest extends TestCase
{
    /**
     * Tests the constructing.
     * @coversNothing
     */
    public function testConstruct(): void
    {
        $entity = new Fluid();

        $this->assertSame('', $entity->getName());
        $this->assertNull($entity->getLocalisedName());
        $this->assertNull($entity->getLocalisedDescription());
    }

    /**
     * Tests the setting and getting the name.
     * @covers ::getName
     * @covers ::setName
     */
    public function testSetAndGetName(): void
    {
        $name = 'abc';
        $entity = new Fluid();

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
        $entity = new Fluid();

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
        $entity = new Fluid();

        $this->assertSame($entity, $entity->setLocalisedDescription($localisedDescription));
        $this->assertSame($localisedDescription, $entity->getLocalisedDescription());
    }
}
