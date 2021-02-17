<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Exception;

use Exception;
use FactorioItemBrowser\Export\Exception\InvalidZipArchiveException;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the InvalidModFileException class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Exception\InvalidZipArchiveException
 */
class InvalidZipArchiveExceptionTest extends TestCase
{
    /**
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $fileName = 'abc';
        $message = 'def';
        $previous = $this->createMock(Exception::class);
        $expectedMessage = 'The zip archive abc could not be processed: def';

        $exception = new InvalidZipArchiveException($fileName, $message, $previous);

        $this->assertSame($expectedMessage, $exception->getMessage());
        $this->assertSame($previous, $exception->getPrevious());
    }
}
