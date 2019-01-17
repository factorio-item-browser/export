<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Parser;

use FactorioItemBrowser\Export\I18n\Translator;
use FactorioItemBrowser\Export\Parser\IconParser;
use FactorioItemBrowser\Export\Parser\ItemParser;
use FactorioItemBrowser\Export\Parser\MachineParser;
use FactorioItemBrowser\Export\Parser\ParserManager;
use FactorioItemBrowser\Export\Parser\ParserManagerFactory;
use FactorioItemBrowser\Export\Parser\RecipeParser;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the ParserManagerFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Parser\ParserManagerFactory
 */
class ParserManagerFactoryTest extends TestCase
{
    /**
     * Tests the invoking.
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        /* @var ContainerInterface|MockObject $container */
        $container = $this->getMockBuilder(ContainerInterface::class)
                          ->setMethods(['get'])
                          ->getMockForAbstractClass();
        $container->expects($this->exactly(5))
                  ->method('get')
                  ->withConsecutive(
                      [Translator::class],
                      [IconParser::class],
                      [ItemParser::class],
                      [MachineParser::class],
                      [RecipeParser::class]
                  )
                  ->willReturnOnConsecutiveCalls(
                      $this->createMock(Translator::class),
                      $this->createMock(IconParser::class),
                      $this->createMock(ItemParser::class),
                      $this->createMock(MachineParser::class),
                      $this->createMock(RecipeParser::class)
                  );

        $factory = new ParserManagerFactory();
        $factory($container, ParserManager::class);
    }
}
