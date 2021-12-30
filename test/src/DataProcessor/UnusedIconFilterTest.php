<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\DataProcessor;

use FactorioItemBrowser\Export\DataProcessor\UnusedIconFilter;
use FactorioItemBrowser\Export\Output\Console;
use FactorioItemBrowser\ExportData\Collection\ChunkedCollection;
use FactorioItemBrowser\ExportData\Entity\Icon;
use FactorioItemBrowser\ExportData\ExportData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the UnusedIconFilter class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Export\DataProcessor\UnusedIconFilter
 */
class UnusedIconFilterTest extends TestCase
{
    /** @var Console&MockObject */
    private Console $console;

    protected function setUp(): void
    {
        $this->console = $this->createMock(Console::class);
    }

    private function createInstance(): UnusedIconFilter
    {
        return new UnusedIconFilter(
            $this->console,
        );
    }

    public function testProcess(): void
    {
        $icon1 = $this->createMock(Icon::class);
        $icon1->id = 'abc';
        $icon2 = $this->createMock(Icon::class);
        $icon2->id = '';
        $icon3 = $this->createMock(Icon::class);
        $icon3->id = 'def';

        $icons = $this->createMock(ChunkedCollection::class);
        $icons->expects($this->once())
            ->method('remove')
            ->with($this->identicalTo($icon2));

        $exportData = $this->createMock(ExportData::class);
        $exportData->expects($this->any())
                   ->method('getIcons')
                   ->willReturn($icons);

        $this->console->expects($this->once())
                      ->method('iterateWithProgressbar')
                      ->with($this->isType('string'), $this->identicalTo($icons))
                      ->willReturnCallback(fn() => yield from [$icon1, $icon2, $icon3]);

        $instance = $this->createInstance();
        $instance->process($exportData);
    }
}
