<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Entity\Dump;

use FactorioItemBrowser\Export\Entity\Dump\DataStage;
use FactorioItemBrowser\Export\Entity\Dump\Icon;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the DataStage class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Entity\Dump\DataStage
 */
class DataStageTest extends TestCase
{
    /**
     * Tests the constructing.
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $entity = new DataStage();

        $this->assertSame([], $entity->getIcons());
    }

    /**
     * Tests the setting and getting the icons.
     * @covers ::getIcons
     * @covers ::setIcons
     */
    public function testSetAndGetIcons(): void
    {
        $icons = [
            $this->createMock(Icon::class),
            $this->createMock(Icon::class),
        ];
        $entity = new DataStage();

        $this->assertSame($entity, $entity->setIcons($icons));
        $this->assertSame($icons, $entity->getIcons());
    }
}
