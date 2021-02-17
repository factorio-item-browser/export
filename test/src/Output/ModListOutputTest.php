<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Output;

use BluePsyduck\FactorioModPortalClient\Entity\Version;
use FactorioItemBrowser\Export\Output\ModListOutput;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * The PHPUnit test of the ModListOutput class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Export\Output\ModListOutput
 */
class ModListOutputTest extends TestCase
{
    public function test(): void
    {
        $output = new BufferedOutput();
        $instance = new ModListOutput($output);

        $result = $instance->add('abc', new Version('1.2.3'), new Version('2.3.4'));
        $this->assertSame($instance, $result);
        $instance->add('def', new Version('3.4.5'), new Version('3.4.5'));

        $result = $instance->render();
        $this->assertSame($instance, $result);

        $content = $output->fetch();

        $this->assertStringContainsString('abc', $content);
        $this->assertStringContainsString('def', $content);
        $this->assertStringContainsString('1.2.3', $content);
        $this->assertStringContainsString('2.3.4', $content);
        $this->assertStringContainsString('3.4.5', $content);
    }
}
