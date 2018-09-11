<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Command\Export;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Export\Command\Export\ExportCombinationCommand;
use FactorioItemBrowser\Export\Factorio\Instance;
use FactorioItemBrowser\Export\Parser\ParserManager;
use FactorioItemBrowser\ExportData\Registry\EntityRegistry;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the ExportCombinationCommand class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Command\Export\ExportCombinationCommand
 */
class ExportCombinationCommandTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests the constructing.
     * @covers ::__construct
     * @throws ReflectionException
     */
    public function testConstruct(): void
    {
        /* @var EntityRegistry $combinationRegistry */
        $combinationRegistry = $this->createMock(EntityRegistry::class);
        /* @var Instance $instance */
        $instance = $this->createMock(Instance::class);
        /* @var ParserManager $parserManager */
        $parserManager = $this->createMock(ParserManager::class);

        $command = new ExportCombinationCommand($combinationRegistry, $instance, $parserManager);
        $this->assertSame($combinationRegistry, $this->extractProperty($command, 'combinationRegistry'));
        $this->assertSame($instance, $this->extractProperty($command, 'instance'));
        $this->assertSame($parserManager, $this->extractProperty($command, 'parserManager'));
    }
}
