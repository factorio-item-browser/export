<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Entity\Dump;

use FactorioItemBrowser\Export\Entity\Dump\Ingredient;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the Ingredient class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Entity\Dump\Ingredient
 */
class IngredientTest extends TestCase
{
    /**
     * Tests the constructing.
     * @coversNothing
     */
    public function testConstruct(): void
    {
        $entity = new Ingredient();

        $this->assertSame('', $entity->getType());
        $this->assertSame('', $entity->getName());
        $this->assertSame(1., $entity->getAmount());
    }

    /**
     * Tests the setting and getting the type.
     * @covers ::getType
     * @covers ::setType
     */
    public function testSetAndGetType(): void
    {
        $type = 'abc';
        $entity = new Ingredient();

        $this->assertSame($entity, $entity->setType($type));
        $this->assertSame($type, $entity->getType());
    }

    /**
     * Tests the setting and getting the name.
     * @covers ::getName
     * @covers ::setName
     */
    public function testSetAndGetName(): void
    {
        $name = 'abc';
        $entity = new Ingredient();

        $this->assertSame($entity, $entity->setName($name));
        $this->assertSame($name, $entity->getName());
    }

    /**
     * Tests the setting and getting the amount.
     * @covers ::getAmount
     * @covers ::setAmount
     */
    public function testSetAndGetAmount(): void
    {
        $amount = 13.37;
        $entity = new Ingredient();

        $this->assertSame($entity, $entity->setAmount($amount));
        $this->assertSame($amount, $entity->getAmount());
    }
}
