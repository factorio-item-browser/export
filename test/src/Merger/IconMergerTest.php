<?php

namespace FactorioItemBrowserTest\Export\Merger;

use FactorioItemBrowser\Export\Merger\IconMerger;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the IconMerger class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Merger\IconMerger
 */
class IconMergerTest extends TestCase
{
    /**
     * Tests the merge method.
     * @covers ::merge
     */
    public function testMerge(): void
    {
        $source = new Combination();
        $source->setIconHashes(['abc', 'def']);

        $destination = new Combination();
        $destination->setIconHashes(['abc', 'ghi']);

        $expectedDestination = new Combination();
        $expectedDestination->setIconHashes(['abc', 'ghi', 'def']);

        $merger = new IconMerger();
        $merger->merge($destination, $source);
        $this->assertEquals($expectedDestination, $destination);
    }

}
