<?php

namespace FactorioItemBrowserTest\Export\Factorio;

use BluePsyduck\Common\Data\DataContainer;
use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Export\Exception\DumpException;
use FactorioItemBrowser\Export\Factorio\DumpExtractor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the DumpExtractor class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Factorio\DumpExtractor
 */
class DumpExtractorTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests the extract method.
     * @throws DumpException
     * @covers ::extract
     */
    public function testExtract(): void
    {
        $output = 'foo';
        $expectedResult = new DataContainer([
            'items' => ['abc' => 'cba'],
            'fluids' => ['def' => 'fed'],
            'recipes' => [
                'normal' => ['ghi' => 'ihg'],
                'expensive' => ['jkl' => 'lkj'],
            ],
            'machines' => ['mno' => 'onm'],
            'icons' => ['pqr' => 'rqp'],
            'fluidBoxes' => ['stu' => 'uts'],
        ]);

        /* @var DumpExtractor|MockObject $dumpExtractor */
        $dumpExtractor = $this->getMockBuilder(DumpExtractor::class)
                              ->setMethods(['extractDumpData'])
                              ->disableOriginalConstructor()
                              ->getMock();
        $dumpExtractor->expects($this->exactly(7))
                      ->method('extractDumpData')
                      ->withConsecutive(
                          [$output, 'ITEMS'],
                          [$output, 'FLUIDS'],
                          [$output, 'RECIPES_NORMAL'],
                          [$output, 'RECIPES_EXPENSIVE'],
                          [$output, 'MACHINES'],
                          [$output, 'ICONS'],
                          [$output, 'FLUID_BOXES']
                      )
                      ->willReturnOnConsecutiveCalls(
                          ['abc' => 'cba'],
                          ['def' => 'fed'],
                          ['ghi' => 'ihg'],
                          ['jkl' => 'lkj'],
                          ['mno' => 'onm'],
                          ['pqr' => 'rqp'],
                          ['stu' => 'uts']
                      );

        $result = $dumpExtractor->extract($output);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the extractDumpData method.
     * @throws ReflectionException
     * @covers ::extractDumpData
     */
    public function testExtractDumpData(): void
    {
        $name = 'abc';
        $output = 'def';
        $rawDump = 'ghi';
        $dumpData = ['jkl' => 'mno'];

        /* @var DumpExtractor|MockObject $dumpExtractor */
        $dumpExtractor = $this->getMockBuilder(DumpExtractor::class)
                              ->setMethods(['extractRawDump', 'parseDump'])
                              ->disableOriginalConstructor()
                              ->getMock();
        $dumpExtractor->expects($this->once())
                      ->method('extractRawDump')
                      ->with($output, $name)
                      ->willReturn($rawDump);
        $dumpExtractor->expects($this->once())
                      ->method('parseDump')
                      ->with($name, $rawDump)
                      ->willReturn($dumpData);

        $result = $this->invokeMethod($dumpExtractor, 'extractDumpData', $output, $name);
        $this->assertSame($dumpData, $result);
    }

    /**
     * Provides the data for the extractRawDump test.
     * @return array
     */
    public function provideExtractRawDump(): array
    {
        return [
            ['abc>def<ghi', 4, 7, false, 'def'],
            ['fail', null, 42, true, null],
            ['fail', 42, null, true, null],
            ['fail', 1337, 42, true, null],
        ];
    }

    /**
     * Tests the extractRawDump method.
     * @param string $output
     * @param int|null $startPosition
     * @param int|null $endPosition
     * @param bool $expectException
     * @param null|string $expectedResult
     * @throws ReflectionException
     * @covers ::extractRawDump
     * @dataProvider provideExtractRawDump
     */
    public function testExtractRawDump(
        string $output,
        ?int $startPosition,
        ?int $endPosition,
        bool $expectException,
        ?string $expectedResult
    ): void {
        $name = 'foo';

        /* @var DumpExtractor|MockObject $dumpExtractor */
        $dumpExtractor = $this->getMockBuilder(DumpExtractor::class)
                              ->setMethods(['getStartPosition', 'getEndPosition'])
                              ->disableOriginalConstructor()
                              ->getMock();
        $dumpExtractor->expects($this->once())
                      ->method('getStartPosition')
                      ->with($output, $name)
                      ->willReturn($startPosition);
        $dumpExtractor->expects($this->once())
                      ->method('getEndPosition')
                      ->with($output, $name)
                      ->willReturn($endPosition);

        if ($expectException) {
            $this->expectException(DumpException::class);
        }

        $result = $this->invokeMethod($dumpExtractor, 'extractRawDump', $output, $name);
        $this->assertSame($expectedResult, $result);
    }

    /**
     * Provides the data for the getStartPosition test.
     * @return array
     */
    public function provideGetStartPosition(): array
    {
        return [
            ['abcFOO>>>---def', 'FOO', 12],
            ['fail', 'FOO', null],
        ];
    }

    /**
     * Tests the getStartPosition method.
     * @param string $output
     * @param string $name
     * @param int|null $expectedResult
     * @throws ReflectionException
     * @covers ::getStartPosition
     * @dataProvider provideGetStartPosition
     */
    public function testGetStartPosition(string $output, string $name, ?int $expectedResult): void
    {
        $dumpExtractor = new DumpExtractor();
        $result = $this->invokeMethod($dumpExtractor, 'getStartPosition', $output, $name);
        $this->assertSame($expectedResult, $result);
    }

    /**
     * Provides the data for the getEndPosition test.
     * @return array
     */
    public function provideGetEndPosition(): array
    {
        return [
            ['abc---<<<FOOdef', 'FOO', 3],
            ['fail', 'FOO', null],
        ];
    }

    /**
     * Tests the getEndPosition method.
     * @param string $output
     * @param string $name
     * @param int|null $expectedResult
     * @throws ReflectionException
     * @covers ::getEndPosition
     * @dataProvider provideGetEndPosition
     */
    public function testGetEndPosition(string $output, string $name, ?int $expectedResult): void
    {
        $dumpExtractor = new DumpExtractor();
        $result = $this->invokeMethod($dumpExtractor, 'getEndPosition', $output, $name);
        $this->assertSame($expectedResult, $result);
    }

    /**
     * Provides the data for the parseDump test.
     * @return array
     */
    public function provideParseDump(): array
    {
        return [
            ['{"abc":"def","ghi":"jkl"}', false, ['abc' => 'def', 'ghi' => 'jkl']],
            ['N;', true, null],
            ['fail"json', true, null],
        ];
    }

    /**
     * Tests the parseDump method.
     * @param string $dump
     * @param bool $expectException
     * @param array|null $expectedResult
     * @throws ReflectionException
     * @covers ::parseDump
     * @dataProvider provideParseDump
     */
    public function testParseDump(string $dump, bool $expectException, ?array $expectedResult): void
    {
        $name = 'foo';

        if ($expectException) {
            $this->expectException(DumpException::class);
        }

        $dumpExtractor = new DumpExtractor();
        $result = $this->invokeMethod($dumpExtractor, 'parseDump', $name, $dump);
        $this->assertEquals($expectedResult, $result);
    }
}
