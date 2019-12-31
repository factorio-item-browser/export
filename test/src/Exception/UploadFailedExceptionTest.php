<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Exception;

use Exception;
use FactorioItemBrowser\Export\Exception\UploadFailedException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the UploadFailedException class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Exception\UploadFailedException
 */
class UploadFailedExceptionTest extends TestCase
{
    /**
     * Tests the constructing.
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $message = 'abc';
        $expectedMessage = 'Upload to the FTP server failed: abc';

        /* @var Exception&MockObject $previous */
        $previous = $this->createMock(Exception::class);

        $exception = new UploadFailedException($message, $previous);

        $this->assertSame($expectedMessage, $exception->getMessage());
        $this->assertSame($previous, $exception->getPrevious());
    }
}
