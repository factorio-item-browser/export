<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Entity;

use BluePsyduck\FactorioModPortalClient\Entity\Version;
use FactorioItemBrowser\Export\Entity\InfoJson;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the InfoJson class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Entity\InfoJson
 */
class InfoJsonTest extends TestCase
{
    /**
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $entity = new InfoJson();
        $this->assertEquals(new Version(), $entity->version);
        $this->assertEquals(new Version(), $entity->factorioVersion);
    }
}
