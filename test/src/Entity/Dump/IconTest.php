<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Entity\Dump;

use FactorioItemBrowser\Export\Entity\Dump\Icon;
use FactorioItemBrowser\Export\Entity\Dump\Layer;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the Icon class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Entity\Dump\Icon
 */
class IconTest extends TestCase
{
    /**
     * Tests the constructing.
     * @coversNothing
     */
    public function testConstruct(): void
    {
        $entity = new Icon();

        $this->assertSame('', $entity->getType());
        $this->assertSame('', $entity->getName());
        $this->assertSame([], $entity->getLayers());
    }

    /**
     * Tests the setting and getting the type.
     * @covers ::getType
     * @covers ::setType
     */
    public function testSetAndGetType(): void
    {
        $type = 'abc';
        $entity = new Icon();

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
        $entity = new Icon();

        $this->assertSame($entity, $entity->setName($name));
        $this->assertSame($name, $entity->getName());
    }

    /**
     * Tests the setting and getting the layers.
     * @covers ::getLayers
     * @covers ::setLayers
     */
    public function testSetAndGetLayers(): void
    {
        $layers = [
            $this->createMock(Layer::class),
            $this->createMock(Layer::class),
        ];
        $entity = new Icon();

        $this->assertSame($entity, $entity->setLayers($layers));
        $this->assertSame($layers, $entity->getLayers());
    }
}
