<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Entity\ModList;

use FactorioItemBrowser\Export\Entity\ModList\Mod;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the Mod class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Entity\ModList\Mod
 */
class ModTest extends TestCase
{
    /**
     * Tests the setting and getting the name.
     * @covers ::getName
     * @covers ::setName
     */
    public function testSetAndGetName(): void
    {
        $name = 'abc';
        $mod = new Mod();

        $this->assertSame($mod, $mod->setName($name));
        $this->assertSame($name, $mod->getName());
    }

    /**
     * Tests the setting and getting the enabled.
     * @covers ::getEnabled
     * @covers ::setEnabled
     */
    public function testSetAndGetEnabled(): void
    {
        $mod = new Mod();

        $this->assertSame($mod, $mod->setEnabled(true));
        $this->assertTrue($mod->getEnabled());
    }
}
