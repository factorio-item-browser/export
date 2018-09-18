<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Parser;

use FactorioItemBrowser\Export\ExportData\RawExportDataService;
use FactorioItemBrowser\Export\I18n\Translator;
use FactorioItemBrowser\Export\Parser\IconParser;
use FactorioItemBrowser\Export\Parser\ItemParser;
use FactorioItemBrowser\Export\Parser\RecipeParser;
use FactorioItemBrowser\Export\Parser\RecipeParserFactory;
use FactorioItemBrowser\ExportData\Registry\EntityRegistry;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the RecipeParserFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Parser\RecipeParserFactory
 */
class RecipeParserFactoryTest extends TestCase
{
    /**
     * Tests the invoking.
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        /* @var EntityRegistry $recipeRegistry */
        $recipeRegistry = $this->createMock(EntityRegistry::class);

        /* @var RawExportDataService|MockObject $rawExportDataService */
        $rawExportDataService = $this->getMockBuilder(RawExportDataService::class)
                                     ->setMethods(['getRecipeRegistry'])
                                     ->disableOriginalConstructor()
                                     ->getMock();
        $rawExportDataService->expects($this->once())
                             ->method('getRecipeRegistry')
                             ->willReturn($recipeRegistry);

        /* @var ContainerInterface|MockObject $container */
        $container = $this->getMockBuilder(ContainerInterface::class)
                          ->setMethods(['get'])
                          ->getMockForAbstractClass();
        $container->expects($this->exactly(4))
                  ->method('get')
                  ->withConsecutive(
                      [IconParser::class],
                      [ItemParser::class],
                      [RawExportDataService::class],
                      [Translator::class]
                  )
                  ->willReturnOnConsecutiveCalls(
                      $this->createMock(IconParser::class),
                      $this->createMock(ItemParser::class),
                      $rawExportDataService,
                      $this->createMock(Translator::class)
                  );

        $factory = new RecipeParserFactory();
        $factory($container, RecipeParser::class);
    }
}
