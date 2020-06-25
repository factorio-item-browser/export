<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Entity;

use FactorioItemBrowser\Export\Entity\ModList\Mod;
use FactorioItemBrowser\Export\Entity\ModListJson;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the ModListJson class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Entity\ModListJson
 */
class ModListJsonTest extends TestCase
{
    /**
     * Tests the setMods method.
     * @covers ::addMod
     * @covers ::getMods
     * @covers ::setMods
     */
    public function testSetAddAndGetMods(): void
    {
        $mod1 = $this->createMock(Mod::class);
        $mod2 = $this->createMock(Mod::class);
        $mod3 = $this->createMock(Mod::class);

        $modList = new ModListJson();
        $this->assertSame($modList, $modList->setMods([$mod1, $mod2]));
        $this->assertSame([$mod1, $mod2], $modList->getMods());

        $this->assertSame($modList, $modList->addMod($mod3));
        $this->assertSame([$mod1, $mod2, $mod3], $modList->getMods());
    }
}
