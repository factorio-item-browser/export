<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Exception;

use Exception;
use FactorioItemBrowser\Export\Exception\MissingModsException;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the MissingModException class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Export\Exception\MissingModsException
 */
class MissingModExceptionTest extends TestCase
{
    public function test(): void
    {
        $modNames = ['abc', 'def'];
        $expectedMessage = 'Mods abc, def cannot be found on the Mod Portal.';

        $previous = $this->createMock(Exception::class);

        $exception = new MissingModsException($modNames, $previous);

        $this->assertSame($expectedMessage, $exception->getMessage());
        $this->assertSame($previous, $exception->getPrevious());
    }
}
