<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Translator;

use BluePsyduck\FactorioTranslator\Translator;
use FactorioItemBrowser\Export\Translator\TranslatorFactory;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the TranslatorFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Translator\TranslatorFactory
 */
class TranslatorFactoryTest extends TestCase
{
    /**
     * Tests the invoking.
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        $container = $this->createMock(ContainerInterface::class);

        $factory = new TranslatorFactory();
        $factory($container, Translator::class, null);

        $this->addToAssertionCount(1);
    }
}
