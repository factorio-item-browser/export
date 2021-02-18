<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Exception;

use BluePsyduck\TestHelper\ReflectionTrait;
use Exception;
use FactorioItemBrowser\Export\Exception\FactorioExecutionException;
use PHPUnit\Framework\TestCase;

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
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $errorMessage = 'def';
        $exitCode = 42;
        $previous = $this->createMock(Exception::class);
        $expectedMessage = "Factorio exited with code 42:\ndef";

        $exception = new FactorioExecutionException($exitCode, $errorMessage, $previous);

        $this->assertSame($expectedMessage, $exception->getMessage());
        $this->assertSame($exitCode, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }
}
