<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Command\Export;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Export\Command\Export\ExportModMetaCommand;
use FactorioItemBrowser\Export\Console\Console;
use FactorioItemBrowser\Export\I18n\Translator;
use FactorioItemBrowser\ExportData\Entity\Mod;
use FactorioItemBrowser\ExportData\Registry\ModRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ZF\Console\Route;

/**
 * The PHPUnit test of the ExportModMetaCommand class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Command\Export\ExportModMetaCommand
 */
class ExportModMetaCommandTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        /* @var ModRegistry $modRegistry */
        $modRegistry = $this->createMock(ModRegistry::class);
        /* @var Translator $translator */
        $translator = $this->createMock(Translator::class);

        $command = new ExportModMetaCommand($modRegistry, $translator);

        $this->assertSame($modRegistry, $this->extractProperty($command, 'modRegistry'));
        $this->assertSame($translator, $this->extractProperty($command, 'translator'));
    }

    /**
     * Tests the processMod method.
     * @throws ReflectionException
     * @covers ::processMod
     */
    public function testProcessMod(): void
    {
        $mod = (new Mod())->setName('abc');

        /* @var Console|MockObject $console */
        $console = $this->getMockBuilder(Console::class)
                        ->setMethods(['writeAction'])
                        ->disableOriginalConstructor()
                        ->getMock();
        $console->expects($this->once())
                ->method('writeAction')
                ->with('Exporting meta data of mod abc');

        /* @var ModRegistry|MockObject $modRegistry */
        $modRegistry = $this->getMockBuilder(ModRegistry::class)
                            ->setMethods(['set', 'saveMods'])
                            ->disableOriginalConstructor()
                            ->getMock();
        $modRegistry->expects($this->once())
                    ->method('set')
                    ->with($mod);
        $modRegistry->expects($this->once())
                    ->method('saveMods');

        /* @var Translator $translator */
        $translator = $this->createMock(Translator::class);

        /* @var ExportModMetaCommand|MockObject $command */
        $command = $this->getMockBuilder(ExportModMetaCommand::class)
                        ->setMethods(['translate'])
                        ->setConstructorArgs([$modRegistry, $translator])
                        ->getMock();
        $command->expects($this->once())
                ->method('translate')
                ->with($mod);
        $this->injectProperty($command, 'console', $console);

        /* @var Route $route */
        $route = $this->createMock(Route::class);

        $this->invokeMethod($command, 'processMod', $route, $mod);
    }

    /**
     * Tests the translate method.
     * @throws ReflectionException
     * @covers ::translate
     */
    public function testTranslate(): void
    {
        $mod = (new Mod())->setName('abc');
        $mod->getTitles()->setTranslation('en', 'def');
        $mod->getDescriptions()->setTranslation('de', 'ghi');

        /* @var Translator|MockObject $translator */
        $translator = $this->getMockBuilder(Translator::class)
                           ->setMethods(['loadFromModNames', 'addTranslationsToEntity'])
                           ->disableOriginalConstructor()
                           ->getMock();
        $translator->expects($this->once())
                   ->method('loadFromModNames')
                   ->with(['abc']);
        $translator->expects($this->exactly(2))
                   ->method('addTranslationsToEntity')
                   ->withConsecutive(
                       [$mod->getTitles(), 'mod-name', ['mod-name.abc']],
                       [$mod->getDescriptions(), 'mod-description', ['mod-description.abc']]
                   );

        /* @var ModRegistry $modRegistry */
        $modRegistry = $this->createMock(ModRegistry::class);

        $command = new ExportModMetaCommand($modRegistry, $translator);

        $this->invokeMethod($command, 'translate', $mod);
    }
}
