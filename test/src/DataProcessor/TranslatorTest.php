<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\DataProcessor;

use BluePsyduck\FactorioTranslator\Translator as FactorioTranslator;
use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Export\DataProcessor\Translator;
use FactorioItemBrowser\Export\Output\Console;
use FactorioItemBrowser\Export\Output\ProgressBar;
use FactorioItemBrowser\ExportData\Collection\DictionaryInterface;
use FactorioItemBrowser\ExportData\Entity\Item;
use FactorioItemBrowser\ExportData\Entity\Machine;
use FactorioItemBrowser\ExportData\Entity\Mod;
use FactorioItemBrowser\ExportData\Entity\Recipe;
use FactorioItemBrowser\ExportData\ExportData;
use FactorioItemBrowser\ExportData\Storage\Storage;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the Translator class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Export\DataProcessor\Translator
 */
class TranslatorTest extends TestCase
{
    use ReflectionTrait;

    /** @var Console&MockObject */
    private Console $console;
    /** @var FactorioTranslator&MockObject */
    private FactorioTranslator $translator;

    protected function setUp(): void
    {
        $this->console = $this->createMock(Console::class);
        $this->translator = $this->createMock(FactorioTranslator::class);
    }

    /**
     * @param array<string> $mockedMethods
     * @return Translator&MockObject
     */
    private function createInstance(array $mockedMethods = []): Translator
    {
        return $this->getMockBuilder(Translator::class)
                    ->onlyMethods($mockedMethods)
                    ->setConstructorArgs([
                        $this->console,
                        $this->translator,
                    ])
                    ->getMock();
    }

    public function testProcess(): void
    {
        $expectedCount = 8;

        $item1 = new Item();
        $item1->localisedName = ['abc'];
        $item1->localisedDescription = ['def'];
        $item2 = new Item();
        $item2->localisedName = ['ghi'];
        $item2->localisedDescription = ['jkl'];

        $machine1 = new Machine();
        $machine1->localisedName = ['mno'];
        $machine1->localisedDescription = ['pqr'];
        $machine2 = new Machine();
        $machine2->localisedName = ['stu'];
        $machine2->localisedDescription = ['vwx'];

        $mod1 = new Mod();
        $mod1->localisedName = ['yza'];
        $mod1->localisedDescription = ['bcd'];
        $mod2 = new Mod();
        $mod2->localisedName = ['efg'];
        $mod2->localisedDescription = ['hij'];

        $recipe1 = new Recipe();
        $recipe1->localisedName = ['klm'];
        $recipe1->localisedDescription = ['nop'];
        $recipe2 = new Recipe();
        $recipe2->localisedName = ['qrs'];
        $recipe2->localisedDescription = ['tuv'];

        $exportData = new ExportData($this->createMock(Storage::class), 'test');
        $exportData->getItems()->add($item1)
                               ->add($item2);
        $exportData->getMachines()->add($machine1)
                                  ->add($machine2);
        $exportData->getMods()->add($mod1)
                              ->add($mod2);
        $exportData->getRecipes()->add($recipe1)
                                 ->add($recipe2);

        $progressBar = $this->createMock(ProgressBar::class);
        $progressBar->expects($this->once())
                    ->method('setNumberOfSteps')
                    ->with($this->identicalTo($expectedCount));
        $progressBar->expects($this->exactly($expectedCount))
                    ->method('finish');

        $this->console->expects($this->once())
                      ->method('createProgressBar')
                      ->with($this->isType('string'))
                      ->willReturn($progressBar);

        $instance = $this->createInstance(['translate']);
        $instance->expects($this->exactly(16))
                 ->method('translate')
                 ->withConsecutive(
                     [$this->identicalTo(['abc']), $this->identicalTo($item1->labels)],
                     [$this->identicalTo(['def']), $this->identicalTo($item1->descriptions)],
                     [$this->identicalTo(['ghi']), $this->identicalTo($item2->labels)],
                     [$this->identicalTo(['jkl']), $this->identicalTo($item2->descriptions)],
                     [$this->identicalTo(['mno']), $this->identicalTo($machine1->labels)],
                     [$this->identicalTo(['pqr']), $this->identicalTo($machine1->descriptions)],
                     [$this->identicalTo(['stu']), $this->identicalTo($machine2->labels)],
                     [$this->identicalTo(['vwx']), $this->identicalTo($machine2->descriptions)],
                     [$this->identicalTo(['yza']), $this->identicalTo($mod1->labels)],
                     [$this->identicalTo(['bcd']), $this->identicalTo($mod1->descriptions)],
                     [$this->identicalTo(['efg']), $this->identicalTo($mod2->labels)],
                     [$this->identicalTo(['hij']), $this->identicalTo($mod2->descriptions)],
                     [$this->identicalTo(['klm']), $this->identicalTo($recipe1->labels)],
                     [$this->identicalTo(['nop']), $this->identicalTo($recipe1->descriptions)],
                     [$this->identicalTo(['qrs']), $this->identicalTo($recipe2->labels)],
                     [$this->identicalTo(['tuv']), $this->identicalTo($recipe2->descriptions)],
                 );

        $instance->process($exportData);
    }

    /**
     * @throws ReflectionException
     */
    public function testTranslate(): void
    {
        $localisedString = ['foo'];
        $locales = ['de', 'en', 'fr', 'ja'];

        $dictionary = $this->createMock(DictionaryInterface::class);
        $dictionary->expects($this->exactly(2))
                   ->method('set')
                   ->withConsecutive(
                       [$this->identicalTo('en'), $this->identicalTo('abc')],
                       [$this->identicalTo('de'), $this->identicalTo('def')],
                   );

        $this->translator->expects($this->any())
                         ->method('getAllLocales')
                         ->willReturn($locales);

        $this->translator->expects($this->any())
                         ->method('translate')
                         ->willReturnMap([
                             ['en', ['foo'], 'abc'],
                             ['de', ['foo'], 'def'],
                             ['fr', ['foo'], ''],
                             ['ja', ['foo'], 'abc'],
                         ]);

        $instance = $this->createInstance();
        $this->invokeMethod($instance, 'translate', $localisedString, $dictionary);
    }
}
