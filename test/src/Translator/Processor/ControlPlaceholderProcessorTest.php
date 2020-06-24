<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Translator\Processor;

use BluePsyduck\FactorioTranslator\Translator;
use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Export\Translator\Processor\ControlPlaceholderProcessor;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the ControlPlaceholderProcessor class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Translator\Processor\ControlPlaceholderProcessor
 */
class ControlPlaceholderProcessorTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests the processControl method.
     * @throws ReflectionException
     * @covers ::processControl
     */
    public function testProcessControl(): void
    {
        $locale = 'abc';
        $controlName = 'def';
        $version = 42;
        $translatedControl = 'ghi';
        $expectedLocalisedString = ['controls.def'];
        $expectedResult = '[ghi]';

        $translator = $this->createMock(Translator::class);
        $translator->expects($this->once())
                   ->method('translateWithFallback')
                   ->with($this->identicalTo($locale), $this->identicalTo($expectedLocalisedString))
                   ->willReturn($translatedControl);

        $processor = new ControlPlaceholderProcessor();
        $processor->setTranslator($translator);

        $result = $this->invokeMethod($processor, 'processControl', $locale, $controlName, $version);

        $this->assertSame($expectedResult, $result);
    }
}
