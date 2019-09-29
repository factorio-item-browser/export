<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Entity\Dump;

use FactorioItemBrowser\Export\Entity\Dump\ControlStage;
use FactorioItemBrowser\Export\Entity\Dump\Fluid;
use FactorioItemBrowser\Export\Entity\Dump\Item;
use FactorioItemBrowser\Export\Entity\Dump\Machine;
use FactorioItemBrowser\Export\Entity\Dump\Recipe;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the ControlStage class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Entity\Dump\ControlStage
 */
class ControlStageTest extends TestCase
{
    /**
     * Tests the constructing.
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $entity = new ControlStage();

        $this->assertSame([], $entity->getItems());
        $this->assertSame([], $entity->getFluids());
        $this->assertSame([], $entity->getMachines());
        $this->assertSame([], $entity->getNormalRecipes());
        $this->assertSame([], $entity->getExpensiveRecipes());
    }

    /**
     * Tests the setting and getting the items.
     * @covers ::getItems
     * @covers ::setItems
     */
    public function testSetAndGetItems(): void
    {
        $items = [
            $this->createMock(Item::class),
            $this->createMock(Item::class),
        ];
        $entity = new ControlStage();

        $this->assertSame($entity, $entity->setItems($items));
        $this->assertSame($items, $entity->getItems());
    }

    /**
     * Tests the setting and getting the fluids.
     * @covers ::getFluids
     * @covers ::setFluids
     */
    public function testSetAndGetFluids(): void
    {
        $fluids = [
            $this->createMock(Fluid::class),
            $this->createMock(Fluid::class),
        ];
        $entity = new ControlStage();

        $this->assertSame($entity, $entity->setFluids($fluids));
        $this->assertSame($fluids, $entity->getFluids());
    }

    /**
     * Tests the setting and getting the machines.
     * @covers ::getMachines
     * @covers ::setMachines
     */
    public function testSetAndGetMachines(): void
    {
        $machines = [
            $this->createMock(Machine::class),
            $this->createMock(Machine::class),
        ];
        $entity = new ControlStage();

        $this->assertSame($entity, $entity->setMachines($machines));
        $this->assertSame($machines, $entity->getMachines());
    }

    /**
     * Tests the setting and getting the normal recipes.
     * @covers ::getNormalRecipes
     * @covers ::setNormalRecipes
     */
    public function testSetAndGetNormalRecipes(): void
    {
        $normalRecipes = [
            $this->createMock(Recipe::class),
            $this->createMock(Recipe::class),
        ];
        $entity = new ControlStage();

        $this->assertSame($entity, $entity->setNormalRecipes($normalRecipes));
        $this->assertSame($normalRecipes, $entity->getNormalRecipes());
    }

    /**
     * Tests the setting and getting the expensive recipes.
     * @covers ::getExpensiveRecipes
     * @covers ::setExpensiveRecipes
     */
    public function testSetAndGetExpensiveRecipes(): void
    {
        $expensiveRecipes = [
            $this->createMock(Recipe::class),
            $this->createMock(Recipe::class),
        ];
        $entity = new ControlStage();

        $this->assertSame($entity, $entity->setExpensiveRecipes($expensiveRecipes));
        $this->assertSame($expensiveRecipes, $entity->getExpensiveRecipes());
    }
}
