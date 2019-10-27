<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Parser;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Export\Helper\HashCalculator;
use FactorioItemBrowser\Export\Parser\IconParser;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the IconParser class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Parser\IconParser
 */
class IconParserTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked hash calculator.
     * @var HashCalculator&MockObject
     */
    protected $hashCalculator;

    /**
     * Sets up the test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->hashCalculator = $this->createMock(HashCalculator::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $parser = new IconParser($this->hashCalculator);

        $this->assertSame($this->hashCalculator, $this->extractProperty($parser, 'hashCalculator'));
    }


}
