<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Reducer\Mod;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Export\Reducer\Mod\CombinationReducer;
use FactorioItemBrowser\ExportData\Entity\Mod;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;
use FactorioItemBrowser\ExportData\Registry\EntityRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the CombinationReducer class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Reducer\Mod\CombinationReducer
 */
class CombinationReducerTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked reduced combination registry.
     * @var EntityRegistry&MockObject
     */
    protected $reducedCombinationRegistry;

    /**
     * Sets up the test case.
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->reducedCombinationRegistry = $this->createMock(EntityRegistry::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $reducer = new CombinationReducer($this->reducedCombinationRegistry);

        $this->assertSame(
            $this->reducedCombinationRegistry,
            $this->extractProperty($reducer, 'reducedCombinationRegistry')
        );
    }

    /**
     * Tests the reduce method.
     * @throws ReflectionException
     * @covers ::reduce
     */
    public function testReduce(): void
    {
        $combinationHashes = ['abc', 'def'];
        $reducedCombinationHashes = ['ghi', 'jkl'];

        /* @var Mod&MockObject $mod */
        $mod = $this->createMock(Mod::class);
        $mod->expects($this->once())
            ->method('getCombinationHashes')
            ->willReturn($combinationHashes);
        $mod->expects($this->once())
            ->method('setCombinationHashes')
            ->with($this->identicalTo($reducedCombinationHashes));

        /* @var CombinationReducer&MockObject $reducer */
        $reducer = $this->getMockBuilder(CombinationReducer::class)
                        ->setMethods(['filterCombinationHashes'])
                        ->setConstructorArgs([$this->reducedCombinationRegistry])
                        ->getMock();
        $reducer->expects($this->once())
                ->method('filterCombinationHashes')
                ->with($this->identicalTo($combinationHashes))
                ->willReturn($reducedCombinationHashes);

        $this->invokeMethod($reducer, 'reduce', $mod);
    }

    /**
     * Tests the filterCombinationHashes method.
     * @throws ReflectionException
     * @covers ::filterCombinationHashes
     */
    public function testFilterCombinationHashes(): void
    {
        $combinationHashes = ['abc', 'def', 'ghi'];
        $expectedResult = ['abc', 'ghi'];

        /* @var Combination&MockObject $combination1 */
        $combination1 = $this->createMock(Combination::class);
        /* @var Combination&MockObject $combination2 */
        $combination2 = $this->createMock(Combination::class);

        $this->reducedCombinationRegistry->expects($this->exactly(3))
                                         ->method('get')
                                         ->withConsecutive(
                                             [$this->identicalTo('abc')],
                                             [$this->identicalTo('def')],
                                             [$this->identicalTo('ghi')]
                                         )
                                         ->willReturnOnConsecutiveCalls(
                                             $combination1,
                                             null,
                                             $combination2
                                         );

        $reducer = new CombinationReducer($this->reducedCombinationRegistry);
        $result = $this->invokeMethod($reducer, 'filterCombinationHashes', $combinationHashes);

        $this->assertEquals($expectedResult, $result);
    }
}
