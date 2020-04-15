<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Entity\Dump;

use FactorioItemBrowser\Export\Entity\Dump\Layer;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the Layer class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Entity\Dump\Layer
 */
class LayerTest extends TestCase
{
    /**
     * Tests the constructing.
     * @coversNothing
     */
    public function testConstruct(): void
    {
        $entity = new Layer();

        $this->assertSame('', $entity->getFile());
        $this->assertSame(0, $entity->getSize());
        $this->assertSame(1., $entity->getScale());
        $this->assertSame(0, $entity->getShiftX());
        $this->assertSame(0, $entity->getShiftY());
        $this->assertSame(1., $entity->getTintRed());
        $this->assertSame(1., $entity->getTintGreen());
        $this->assertSame(1., $entity->getTintBlue());
        $this->assertSame(1., $entity->getTintAlpha());
    }

    /**
     * Tests the setting and getting the file.
     * @covers ::getFile
     * @covers ::setFile
     */
    public function testSetAndGetFile(): void
    {
        $file = 'abc';
        $entity = new Layer();
    
        $this->assertSame($entity, $entity->setFile($file));
        $this->assertSame($file, $entity->getFile());
    }

    /**
     * Tests the setting and getting the size.
     * @covers ::getSize
     * @covers ::setSize
     */
    public function testSetAndGetSize(): void
    {
        $size = 42;
        $entity = new Layer();

        $this->assertSame($entity, $entity->setSize($size));
        $this->assertSame($size, $entity->getSize());
    }

    /**
     * Tests the setting and getting the scale.
     * @covers ::getScale
     * @covers ::setScale
     */
    public function testSetAndGetScale(): void
    {
        $scale = 13.37;
        $entity = new Layer();

        $this->assertSame($entity, $entity->setScale($scale));
        $this->assertSame($scale, $entity->getScale());
    }

    /**
     * Tests the setting and getting the shift x.
     * @covers ::getShiftX
     * @covers ::setShiftX
     */
    public function testSetAndGetShiftX(): void
    {
        $shiftX = 42;
        $entity = new Layer();

        $this->assertSame($entity, $entity->setShiftX($shiftX));
        $this->assertSame($shiftX, $entity->getShiftX());
    }

    /**
     * Tests the setting and getting the shift y.
     * @covers ::getShiftY
     * @covers ::setShiftY
     */
    public function testSetAndGetShiftY(): void
    {
        $shiftY = 42;
        $entity = new Layer();

        $this->assertSame($entity, $entity->setShiftY($shiftY));
        $this->assertSame($shiftY, $entity->getShiftY());
    }

    /**
     * Tests the setting and getting the tint red.
     * @covers ::getTintRed
     * @covers ::setTintRed
     */
    public function testSetAndGetTintRed(): void
    {
        $tintRed = 13.37;
        $entity = new Layer();
    
        $this->assertSame($entity, $entity->setTintRed($tintRed));
        $this->assertSame($tintRed, $entity->getTintRed());
    }

    /**
     * Tests the setting and getting the tint green.
     * @covers ::getTintGreen
     * @covers ::setTintGreen
     */
    public function testSetAndGetTintGreen(): void
    {
        $tintGreen = 13.37;
        $entity = new Layer();

        $this->assertSame($entity, $entity->setTintGreen($tintGreen));
        $this->assertSame($tintGreen, $entity->getTintGreen());
    }

    /**
     * Tests the setting and getting the tint blue.
     * @covers ::getTintBlue
     * @covers ::setTintBlue
     */
    public function testSetAndGetTintBlue(): void
    {
        $tintBlue = 13.37;
        $entity = new Layer();

        $this->assertSame($entity, $entity->setTintBlue($tintBlue));
        $this->assertSame($tintBlue, $entity->getTintBlue());
    }

    /**
     * Tests the setting and getting the tint alpha.
     * @covers ::getTintAlpha
     * @covers ::setTintAlpha
     */
    public function testSetAndGetTintAlpha(): void
    {
        $tintAlpha = 13.37;
        $entity = new Layer();

        $this->assertSame($entity, $entity->setTintAlpha($tintAlpha));
        $this->assertSame($tintAlpha, $entity->getTintAlpha());
    }
}
