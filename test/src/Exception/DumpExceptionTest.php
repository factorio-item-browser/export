<?php

namespace FactorioItemBrowserTest\Export\Exception;

use BluePsyduck\Common\Test\ReflectionTrait;
use Exception;
use FactorioItemBrowser\Export\Exception\DumpException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the DumpException class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Exception\DumpException
 */
class DumpExceptionTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests the constructing.
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $name = 'abc';
        $message = 'def';
        $output = 'ghi';
        $builtMessage = 'jkl';
        $previous = new Exception('foo');

        /* @var DumpException|MockObject $exception */
        $exception = $this->getMockBuilder(DumpException::class)
                          ->setMethods(['buildMessage'])
                          ->disableOriginalConstructor()
                          ->getMock();
        $exception->expects($this->once())
                  ->method('buildMessage')
                  ->with($name, $message, $output)
                  ->willReturn($builtMessage);

        $exception->__construct($name, $message, $output, $previous);
        $this->assertSame($exception->getMessage(), $builtMessage);
        $this->assertSame(0, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    /**
     * Provides the data for the buildMessage test.
     * @return array
     */
    public function provideBuildMessage(): array
    {
        return [
            ['foo', true],
            ['', false],
        ];
    }

    /**
     * Tests the buildMessage method.
     * @param string $output
     * @param bool $expectOutput
     * @throws ReflectionException
     * @covers ::buildMessage
     * @dataProvider provideBuildMessage
     */
    public function testBuildMessage(string $output, bool $expectOutput): void
    {
        $name = 'abc';
        $message = 'def';
        $extractedOutput = ['ghi', 'jkl'];

        if ($expectOutput) {
            $expectedResult = 'Failed to extract dump abc: def'
                . PHP_EOL . 'Last lines of output: '
                . PHP_EOL . 'ghi' . PHP_EOL . 'jkl';
        } else {
            $expectedResult = 'Failed to extract dump abc: def';
        }

        /* @var DumpException|MockObject $exception */
        $exception = $this->getMockBuilder(DumpException::class)
                          ->setMethods(['extractLastOutputLines'])
                          ->disableOriginalConstructor()
                          ->getMock();
        $exception->expects($expectOutput ? $this->once() : $this->never())
                  ->method('extractLastOutputLines')
                  ->with($output)
                  ->willReturn($extractedOutput);

        $result = $this->invokeMethod($exception, 'buildMessage', $name, $message, $output);
        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the extractLastOutputLines method.
     * @throws ReflectionException
     * @covers ::extractLastOutputLines
     */
    public function testExtractLastOutputLines(): void
    {
        $output = implode(PHP_EOL, ['abc', 'def', 'ghi', 'jkl', 'mno', 'pqr', 'stu']);
        $expectedResult = ['ghi', 'jkl', 'mno', 'pqr', 'stu'];

        $exception = new DumpException('foo', 'bar');
        $result = $this->invokeMethod($exception, 'extractLastOutputLines', $output);
        $this->assertEquals($expectedResult, $result);
    }
}
