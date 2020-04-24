<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Exception;

use BluePsyduck\TestHelper\ReflectionTrait;
use Exception;
use FactorioItemBrowser\Export\Exception\FactorioExecutionException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the FactorioExecutionException class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Exception\FactorioExecutionException
 */
class FactorioExecutionExceptionTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests the constructing.
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $output = 'abc';
        $errorMessage = 'def';
        $exitCode = 42;
        $expectedMessage = 'Factorio exited with code 42: def';

        /* @var Exception&MockObject $previous */
        $previous = $this->createMock(Exception::class);

        /* @var FactorioExecutionException&MockObject $exception */
        $exception = $this->getMockBuilder(FactorioExecutionException::class)
                          ->onlyMethods(['extractErrorMessageFromOutput'])
                          ->disableOriginalConstructor()
                          ->getMock();
        $exception->expects($this->once())
                  ->method('extractErrorMessageFromOutput')
                  ->with($this->identicalTo($output))
                  ->willReturn($errorMessage);

        $exception->__construct($exitCode, $output, $previous);

        $this->assertSame($expectedMessage, $exception->getMessage());
        $this->assertSame($exitCode, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    /**
     * Provides the data for the extractErrorMessageFromOutput test.
     * @return array<mixed>
     */
    public function provideExtractErrorMessageFromOutput(): array
    {
        $output1 = <<<EOT
   0.007 Running in headless mode
   0.009 Loading mod core 0.0.0 (data.lua)
   0.061 Loading mod base 0.18.21 (data.lua)
   0.235 Error Util.cpp:83: Failed to load mod "base": __base__/data.lua:89: __base__/prototypes/...
stack traceback:
	[C]: in function 'require'
	__base__/data.lua:89: in main chunk
EOT;
        $message1 = <<<EOT
   0.235 Error Util.cpp:83: Failed to load mod "base": __base__/data.lua:89: __base__/prototypes/...
stack traceback:
	[C]: in function 'require'
	__base__/data.lua:89: in main chunk
EOT;

        $output2 = <<<EOT
   2.399 Checksum for script /project/data/instances/2f4a45fa-a509-a9d1-aae6-ffcf984a7a76/...
   2.401 Checksum for script __Dump__/control.lua: 3285258963
Error: The mod Factorio Item Browser - Dump (1.0.0) caused a non-recoverable error.
Please report this error to the mod author.

Error while running event Dump::on_init()
__Dump__/map.lua:82: attempt to index global 'prototype2' (a nil value)
stack traceback:
	__Dump__/map.lua:82: in function 'recipe'
	__Dump__/control.lua:37: in function <__Dump__/control.lua:4>
   2.448 Goodbye
EOT;
        $message2 = <<<EOT
Error: The mod Factorio Item Browser - Dump (1.0.0) caused a non-recoverable error.
Please report this error to the mod author.

Error while running event Dump::on_init()
__Dump__/map.lua:82: attempt to index global 'prototype2' (a nil value)
stack traceback:
	__Dump__/map.lua:82: in function 'recipe'
	__Dump__/control.lua:37: in function <__Dump__/control.lua:4>
   2.448 Goodbye
EOT;

        $output3 = <<<EOT
Lorem ipsum dolor sit amet.
>>>TEST>>>Actual dumped data we do not want to see in the error<<<TEST<<<
foo
bar
   2.448 Goodbye
EOT;
        $message3 = <<<EOT
foo
bar
   2.448 Goodbye
EOT;

        return [
            [$output1, $message1],
            [$output2, $message2],
            [$output3, $message3],
        ];
    }

    /**
     * Tests the extractErrorMessageFromOutput method.
     * @param string $output
     * @param string $expectedResult
     * @throws ReflectionException
     * @covers ::extractErrorMessageFromOutput
     * @dataProvider provideExtractErrorMessageFromOutput
     */
    public function testExtractErrorMessageFromOutput(string $output, string $expectedResult): void
    {
        /* @var FactorioExecutionException&MockObject $exception */
        $exception = $this->getMockBuilder(FactorioExecutionException::class)
                          ->onlyMethods(['__construct'])
                          ->disableOriginalConstructor()
                          ->getMock();

        $result = $this->invokeMethod($exception, 'extractErrorMessageFromOutput', $output);

        $this->assertSame($expectedResult, $result);
    }
}
