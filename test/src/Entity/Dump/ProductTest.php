<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Entity\Dump;

use FactorioItemBrowser\Export\Entity\Dump\Product;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the Product class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Entity\Dump\Product
 */
class ProductTest extends TestCase
{
    /**
     * Tests the constructing.
     * @coversNothing
     */
    public function testConstruct(): void
    {
        $entity = new Product();

        $this->assertSame('', $entity->getType());
        $this->assertSame('', $entity->getName());
        $this->assertSame(1., $entity->getAmountMin());
        $this->assertSame(1., $entity->getAmountMax());
        $this->assertSame(1., $entity->getProbability());
    }

    /**
     * Tests the setting and getting the type.
     * @covers ::getType
     * @covers ::setType
     */
    public function testSetAndGetType(): void
    {
        $type = 'abc';
        $entity = new Product();

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
        $entity = new Product();

        $this->assertSame($entity, $entity->setName($name));
        $this->assertSame($name, $entity->getName());
    }

    /**
     * Tests the setting and getting the amount min.
     * @covers ::getAmountMin
     * @covers ::setAmountMin
     */
    public function testSetAndGetAmountMin(): void
    {
        $amountMin = 13.37;
        $entity = new Product();

        $this->assertSame($entity, $entity->setAmountMin($amountMin));
        $this->assertSame($amountMin, $entity->getAmountMin());
    }

    /**
     * Tests the setting and getting the amount max.
     * @covers ::getAmountMax
     * @covers ::setAmountMax
     */
    public function testSetAndGetAmountMax(): void
    {
        $amountMax = 13.37;
        $entity = new Product();

        $this->assertSame($entity, $entity->setAmountMax($amountMax));
        $this->assertSame($amountMax, $entity->getAmountMax());
    }

    /**
     * Tests the setting and getting the probability.
     * @covers ::getProbability
     * @covers ::setProbability
     */
    public function testSetAndGetProbability(): void
    {
        $probability = 13.37;
        $entity = new Product();

        $this->assertSame($entity, $entity->setProbability($probability));
        $this->assertSame($probability, $entity->getProbability());
    }
}
