<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Parser;

use BluePsyduck\Common\Data\DataContainer;
use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\I18n\Translator;
use FactorioItemBrowser\Export\Parser\ParserInterface;
use FactorioItemBrowser\Export\Parser\ParserManager;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;
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
     * Tests the constructing.
     * @covers ::__construct
     * @throws ReflectionException
     */
    public function testConstruct(): void
    {
        /* @var Translator $translator */
        $translator = $this->createMock(Translator::class);
        /* @var array|ParserInterface[] $parsers */
        $parsers = [
            $this->createMock(ParserInterface::class),
            $this->createMock(ParserInterface::class),
        ];
        

        $parserManager = new ParserManager($translator, $parsers);
        $this->assertSame($translator, $this->extractProperty($parserManager, 'translator'));
        $this->assertSame($parsers, $this->extractProperty($parserManager, 'parsers'));
    }

    /**
     * Tests the parse method.
     * @covers ::parse
     * @throws ExportException
     */
    public function testParse(): void
    {
        $loadedModNames = ['abc', 'def'];
        $dumpData = new DataContainer(['ghi' => 'jkl']);
        $combination = new Combination();
        $combination->setLoadedModNames($loadedModNames);

        /* @var Translator|MockObject $translator */
        $translator = $this->getMockBuilder(Translator::class)
                           ->setMethods(['loadFromModNames'])
                           ->disableOriginalConstructor()
                           ->getMock();
        $translator->expects($this->once())
                   ->method('loadFromModNames')
                   ->with($loadedModNames);

        /* @var ParserInterface|MockObject $parser1 */
        $parser1 = $this->getMockBuilder(ParserInterface::class)
                        ->setMethods(['parse'])
                        ->getMockForAbstractClass();
        $parser1->expects($this->once())
                ->method('parse')
                ->with($combination, $dumpData);
        /* @var ParserInterface|MockObject $parser2 */
        $parser2 = $this->getMockBuilder(ParserInterface::class)
                        ->setMethods(['parse'])
                        ->getMockForAbstractClass();
        $parser2->expects($this->once())
                ->method('parse')
                ->with($combination, $dumpData);

        $parserManager = new ParserManager($translator, [$parser1, $parser2]);
        $parserManager->parse($combination, $dumpData);
    }
}
