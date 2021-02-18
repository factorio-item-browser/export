<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Exception;

use Exception;
use FactorioItemBrowser\Export\Exception\ZipExtractException;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the ZipExtractException class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Exception\ZipExtractException
 */
class ZipExtractExceptionTest extends TestCase
{
    /**
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $zipFileName = 'abc';
        $message = 'def';
        $previous = $this->createMock(Exception::class);
        $expectedMessage = 'Failed to extract files from zip archive abc: def';

        $exception = new ZipExtractException($zipFileName, $message, $previous);

        $this->assertSame($expectedMessage, $exception->getMessage());
        $this->assertSame($previous, $exception->getPrevious());
    }
}
