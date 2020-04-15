<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Entity\Dump;

use FactorioItemBrowser\Export\Entity\Dump\Machine;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the Machine class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Entity\Dump\Machine
 */
class MachineTest extends TestCase
{
    /**
     * Tests the constructing.
     * @coversNothing
     */
    public function testConstruct(): void
    {
        $entity = new Machine();

        $this->assertSame('', $entity->getName());
        $this->assertNull($entity->getLocalisedName());
        $this->assertNull($entity->getLocalisedDescription());
        $this->assertSame([], $entity->getCraftingCategories());
        $this->assertSame(1., $entity->getCraftingSpeed());
        $this->assertSame(0, $entity->getItemSlots());
        $this->assertSame(0, $entity->getFluidInputSlots());
        $this->assertSame(0, $entity->getFluidOutputSlots());
        $this->assertSame(0, $entity->getModuleSlots());
        $this->assertSame(0., $entity->getEnergyUsage());
    }

    /**
     * Tests the setting and getting the name.
     * @covers ::getName
     * @covers ::setName
     */
    public function testSetAndGetName(): void
    {
        $name = 'abc';
        $entity = new Machine();

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
        $entity = new Machine();

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
        $entity = new Machine();

        $this->assertSame($entity, $entity->setLocalisedDescription($localisedDescription));
        $this->assertSame($localisedDescription, $entity->getLocalisedDescription());
    }

    /**
     * Tests the setting and getting the crafting categories.
     * @covers ::getCraftingCategories
     * @covers ::setCraftingCategories
     */
    public function testSetAndGetCraftingCategories(): void
    {
        $craftingCategories = ['abc', 'def'];
        $entity = new Machine();

        $this->assertSame($entity, $entity->setCraftingCategories($craftingCategories));
        $this->assertSame($craftingCategories, $entity->getCraftingCategories());
    }

    /**
     * Tests the setting and getting the crafting speed.
     * @covers ::getCraftingSpeed
     * @covers ::setCraftingSpeed
     */
    public function testSetAndGetCraftingSpeed(): void
    {
        $craftingSpeed = 13.37;
        $entity = new Machine();

        $this->assertSame($entity, $entity->setCraftingSpeed($craftingSpeed));
        $this->assertSame($craftingSpeed, $entity->getCraftingSpeed());
    }

    /**
     * Tests the setting and getting the item slots.
     * @covers ::getItemSlots
     * @covers ::setItemSlots
     */
    public function testSetAndGetItemSlots(): void
    {
        $itemSlots = 42;
        $entity = new Machine();

        $this->assertSame($entity, $entity->setItemSlots($itemSlots));
        $this->assertSame($itemSlots, $entity->getItemSlots());
    }

    /**
     * Tests the setting and getting the fluid input slots.
     * @covers ::getFluidInputSlots
     * @covers ::setFluidInputSlots
     */
    public function testSetAndGetFluidInputSlots(): void
    {
        $fluidInputSlots = 42;
        $entity = new Machine();

        $this->assertSame($entity, $entity->setFluidInputSlots($fluidInputSlots));
        $this->assertSame($fluidInputSlots, $entity->getFluidInputSlots());
    }

    /**
     * Tests the setting and getting the fluid output slots.
     * @covers ::getFluidOutputSlots
     * @covers ::setFluidOutputSlots
     */
    public function testSetAndGetFluidOutputSlots(): void
    {
        $fluidOutputSlots = 42;
        $entity = new Machine();

        $this->assertSame($entity, $entity->setFluidOutputSlots($fluidOutputSlots));
        $this->assertSame($fluidOutputSlots, $entity->getFluidOutputSlots());
    }

    /**
     * Tests the setting and getting the module slots.
     * @covers ::getModuleSlots
     * @covers ::setModuleSlots
     */
    public function testSetAndGetModuleSlots(): void
    {
        $moduleSlots = 42;
        $entity = new Machine();

        $this->assertSame($entity, $entity->setModuleSlots($moduleSlots));
        $this->assertSame($moduleSlots, $entity->getModuleSlots());
    }

    /**
     * Tests the setting and getting the energy usage.
     * @covers ::getEnergyUsage
     * @covers ::setEnergyUsage
     */
    public function testSetAndGetEnergyUsage(): void
    {
        $energyUsage = 13.37;
        $entity = new Machine();

        $this->assertSame($entity, $entity->setEnergyUsage($energyUsage));
        $this->assertSame($energyUsage, $entity->getEnergyUsage());
    }
}
