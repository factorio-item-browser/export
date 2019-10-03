<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Entity\Dump;

use FactorioItemBrowser\Export\Entity\Dump\ControlStage;
use FactorioItemBrowser\Export\Entity\Dump\DataStage;
use FactorioItemBrowser\Export\Entity\Dump\Dump;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the Dump class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Entity\Dump\Dump
 */
class DumpTest extends TestCase
{
    /**
     * Tests the constructing.
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $entity = new Dump();

        $this->assertSame([], $entity->getModNames());

        // Asserted through type-hinting
        $entity->getDataStage();
        $entity->getControlStage();
    }

    /**
     * Tests the setting and getting the mod names.
     * @covers ::getModNames
     * @covers ::setModNames
     */
    public function testSetAndGetModNames(): void
    {
        $modNames = ['abc', 'def'];
        $entity = new Dump();

        $this->assertSame($entity, $entity->setModNames($modNames));
        $this->assertSame($modNames, $entity->getModNames());
    }

    /**
     * Tests the setting and getting the data stage.
     * @covers ::getDataStage
     * @covers ::setDataStage
     */
    public function testSetAndGetDataStage(): void
    {
        /* @var DataStage&MockObject $dataStage */
        $dataStage = $this->createMock(DataStage::class);
        $entity = new Dump();

        $this->assertSame($entity, $entity->setDataStage($dataStage));
        $this->assertSame($dataStage, $entity->getDataStage());
    }

    /**
     * Tests the setting and getting the control stage.
     * @covers ::getControlStage
     * @covers ::setControlStage
     */
    public function testSetAndGetControlStage(): void
    {
        /* @var ControlStage&MockObject $controlStage */
        $controlStage = $this->createMock(ControlStage::class);
        $entity = new Dump();

        $this->assertSame($entity, $entity->setControlStage($controlStage));
        $this->assertSame($controlStage, $entity->getControlStage());
    }
}
