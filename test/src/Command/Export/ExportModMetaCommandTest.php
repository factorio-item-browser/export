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
     * The mocked mod registry.
     * @var ModRegistry&MockObject
     */
    protected $modRegistry;

    /**
     * The mocked translator.
     * @var Translator&MockObject
     */
    protected $translator;

    /**
     * Sets up the test case.
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->modRegistry = $this->createMock(ModRegistry::class);
        $this->translator = $this->createMock(Translator::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $command = new ExportModMetaCommand($this->modRegistry, $this->translator);

        $this->assertSame($this->modRegistry, $this->extractProperty($command, 'modRegistry'));
        $this->assertSame($this->translator, $this->extractProperty($command, 'translator'));
    }

    /**
     * Tests the processMod method.
     * @throws ReflectionException
     * @covers ::processMod
     */
    public function testProcessMod(): void
    {
        $modName = 'abc';

        /* @var Route $route */
        $route = $this->createMock(Route::class);

        /* @var Mod&MockObject $mod */
        $mod = $this->createMock(Mod::class);
        $mod->expects($this->once())
            ->method('getName')
            ->willReturn($modName);

        /* @var Console&MockObject $console */
        $console = $this->createMock(Console::class);
        $console->expects($this->once())
                ->method('writeAction')
                ->with($this->identicalTo('Exporting meta data of mod abc'));

        /* @var ExportModMetaCommand|MockObject $command */
        $command = $this->getMockBuilder(ExportModMetaCommand::class)
                        ->setMethods(['translate', 'persistMod'])
                        ->setConstructorArgs([$this->modRegistry, $this->translator])
                        ->getMock();
        $command->expects($this->once())
                ->method('translate')
                ->with($this->identicalTo($mod));
        $command->expects($this->once())
                ->method('persistMod')
                ->with($this->identicalTo($mod));
        $this->injectProperty($command, 'console', $console);

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

        $this->translator->expects($this->once())
                         ->method('loadFromModNames')
                         ->with($this->identicalTo(['abc']));
        $this->translator->expects($this->exactly(2))
                         ->method('addTranslationsToEntity')
                         ->withConsecutive(
                             [
                                 $this->identicalTo($mod->getTitles()),
                                 $this->identicalTo('mod-name'),
                                 $this->identicalTo(['mod-name.abc'])
                             ],
                             [
                                 $this->identicalTo($mod->getDescriptions()),
                                 $this->identicalTo('mod-description'),
                                 $this->identicalTo(['mod-description.abc'])
                             ]
                         );

        $command = new ExportModMetaCommand($this->modRegistry, $this->translator);
        $this->invokeMethod($command, 'translate', $mod);
    }
}
