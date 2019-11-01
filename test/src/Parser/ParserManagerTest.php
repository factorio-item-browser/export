<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Parser;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Export\Console\Console;
use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Parser\ParserInterface;
use FactorioItemBrowser\Export\Parser\ParserManager;
use FactorioItemBrowser\ExportData\Entity\Combination;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the ParserManager class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Parser\ParserManager
 */
class ParserManagerTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked console.
     * @var Console&MockObject
     */
    protected $console;

    /**
     * Sets up the test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->console = $this->createMock(Console::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $parsers = [
            $this->createMock(ParserInterface::class),
            $this->createMock(ParserInterface::class),
        ];

        $manager = new ParserManager($this->console, $parsers);

        $this->assertSame($this->console, $this->extractProperty($manager, 'console'));
        $this->assertSame($parsers, $this->extractProperty($manager, 'parsers'));
    }

    /**
     * Tests the parse method.
     * @throws ExportException
     * @covers ::parse
     */
    public function testParse(): void
    {
        /* @var Dump&MockObject $dump */
        $dump = $this->createMock(Dump::class);
        /* @var Combination&MockObject $combination */
        $combination = $this->createMock(Combination::class);

        /* @var ParserInterface&MockObject $parser1 */
        $parser1 = $this->createMock(ParserInterface::class);
        $parser1->expects($this->once())
                ->method('prepare')
                ->with($this->identicalTo($dump));
        $parser1->expects($this->once())
                ->method('parse')
                ->with($this->identicalTo($dump), $this->identicalTo($combination));
        $parser1->expects($this->once())
                ->method('validate')
                ->with($this->identicalTo($combination));

        /* @var ParserInterface&MockObject $parser2 */
        $parser2 = $this->createMock(ParserInterface::class);
        $parser2->expects($this->once())
                ->method('prepare')
                ->with($this->identicalTo($dump));
        $parser2->expects($this->once())
                ->method('parse')
                ->with($this->identicalTo($dump), $this->identicalTo($combination));
        $parser2->expects($this->once())
                ->method('validate')
                ->with($this->identicalTo($combination));

        $parsers = [$parser1, $parser2];

        $this->console->expects($this->exactly(3))
                      ->method('writeAction')
                      ->withConsecutive(
                          [$this->identicalTo('Preparing')],
                          [$this->identicalTo('Parsing')],
                          [$this->identicalTo('Validating')]
                      );

        $manager = new ParserManager($this->console, $parsers);
        $manager->parse($dump, $combination);
    }
}
