<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\I18n;

use FactorioItemBrowser\Export\ExportData\RawExportDataService;
use FactorioItemBrowser\Export\I18n\Translator;
use FactorioItemBrowser\Export\I18n\TranslatorFactory;
use FactorioItemBrowser\Export\Mod\LocaleReader;
use FactorioItemBrowser\ExportData\Registry\ModRegistry;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Zend\I18n\Translator\Translator as ZendTranslator;
use Zend\I18n\Translator\TranslatorInterface;

/**
 * The PHPUnit test of the TranslatorFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\I18n\TranslatorFactory
 */
class TranslatorFactoryTest extends TestCase
{
    /**
     * Tests the invoking.
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        /* @var ModRegistry $modRegistry */
        $modRegistry = $this->createMock(ModRegistry::class);

        /* @var RawExportDataService|MockObject $rawExportDataService */
        $rawExportDataService = $this->getMockBuilder(RawExportDataService::class)
                                     ->setMethods(['getModRegistry'])
                                     ->disableOriginalConstructor()
                                     ->getMock();
        $rawExportDataService->expects($this->once())
                             ->method('getModRegistry')
                             ->willReturn($modRegistry);


        /* @var ContainerInterface|MockObject $container */
        $container = $this->getMockBuilder(ContainerInterface::class)
                          ->setMethods(['get'])
                          ->getMockForAbstractClass();
        $container->expects($this->exactly(3))
                  ->method('get')
                  ->withConsecutive(
                      [LocaleReader::class],
                      [RawExportDataService::class],
                      [TranslatorInterface::class]
                  )
                  ->willReturnOnConsecutiveCalls(
                      $this->createMock(LocaleReader::class),
                      $rawExportDataService,
                      $this->createMock(ZendTranslator::class)
                  );

        $factory = new TranslatorFactory();
        $factory($container, Translator::class);
    }
}
