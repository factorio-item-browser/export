<?php

namespace FactorioItemBrowserTest\Export\Reducer;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Export\Exception\ReducerException;
use FactorioItemBrowser\Export\Reducer\AbstractIdentifiedEntityReducer;
use FactorioItemBrowser\ExportData\Entity\EntityWithIdentifierInterface;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;
use FactorioItemBrowser\ExportData\Registry\EntityRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the AbstractIdentifiedEntityReducer class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Reducer\AbstractIdentifiedEntityReducer
 */
class AbstractIdentifiedEntityReducerTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        /* @var EntityRegistry $rawEntityRegistry */
        $rawEntityRegistry = $this->createMock(EntityRegistry::class);
        /* @var EntityRegistry $reducedEntityRegistry */
        $reducedEntityRegistry = $this->createMock(EntityRegistry::class);

        /* @var AbstractIdentifiedEntityReducer|MockObject $reducer */
        $reducer = $this->getMockBuilder(AbstractIdentifiedEntityReducer::class)
                        ->setConstructorArgs([$rawEntityRegistry, $reducedEntityRegistry])
                        ->getMockForAbstractClass();

        $this->assertSame($rawEntityRegistry, $this->extractProperty($reducer, 'rawEntityRegistry'));
        $this->assertSame($reducedEntityRegistry, $this->extractProperty($reducer, 'reducedEntityRegistry'));
    }

    /**
     * Tests the reduce method.
     * @throws ReflectionException
     * @covers ::reduce
     */
    public function testReduce(): void
    {
        $parentHashes = ['abc', 'def'];
        $mappedParentHashes = ['foo' => 'abc', 'bar' => 'def'];
        $hashes = ['abc', 'ghi', 'jkl'];
        $expectedHashes = ['ghi', 'jkl'];

        /* @var EntityWithIdentifierInterface|MockObject $entity1 */
        $entity1 = $this->getMockBuilder(EntityWithIdentifierInterface::class)
                        ->setMethods(['getIdentifier'])
                        ->getMockForAbstractClass();
        $entity1->expects($this->once())
                ->method('getIdentifier')
                ->willReturn('foo');

        /* @var EntityWithIdentifierInterface|MockObject $entity2 */
        $entity2 = $this->getMockBuilder(EntityWithIdentifierInterface::class)
                        ->setMethods(['getIdentifier'])
                        ->getMockForAbstractClass();
        $entity2->expects($this->once())
                ->method('getIdentifier')
                ->willReturn('bar');

        /* @var EntityWithIdentifierInterface|MockObject $entity3 */
        $entity3 = $this->getMockBuilder(EntityWithIdentifierInterface::class)
                        ->setMethods(['getIdentifier'])
                        ->getMockForAbstractClass();
        $entity3->expects($this->once())
                ->method('getIdentifier')
                ->willReturn('baz');

        /* @var EntityWithIdentifierInterface $parentEntity2 */
        $parentEntity2 = $this->createMock(EntityWithIdentifierInterface::class);

        /* @var EntityRegistry|MockObject $reducedEntityRegistry */
        $reducedEntityRegistry = $this->getMockBuilder(EntityRegistry::class)
                                      ->setMethods(['set'])
                                      ->disableOriginalConstructor()
                                      ->getMock();
        $reducedEntityRegistry->expects($this->exactly(2))
            ->method('set')
            ->withConsecutive(
                [$this->equalTo($entity2)],
                [$entity3]
            )
            ->willReturnOnConsecutiveCalls(
                'ghi',
                'jkl'
            );

        /* @var Combination $combination */
        $combination = $this->createMock(Combination::class);
        /* @var Combination $parentCombination */
        $parentCombination = $this->createMock(Combination::class);
        /* @var EntityRegistry $rawEntityRegistry */
        $rawEntityRegistry = $this->createMock(EntityRegistry::class);

        /* @var AbstractIdentifiedEntityReducer|MockObject $reducer */
        $reducer = $this->getMockBuilder(AbstractIdentifiedEntityReducer::class)
                        ->setMethods([
                            'getHashesFromCombination',
                            'mapEntityHashes',
                            'fetchEntityFromHash',
                            'reduceEntity',
                            'setHashesToCombination'
                        ])
                        ->setConstructorArgs([$rawEntityRegistry, $reducedEntityRegistry])
                        ->getMockForAbstractClass();
        $reducer->expects($this->exactly(2))
            ->method('getHashesFromCombination')
            ->withConsecutive(
                [$parentCombination],
                [$combination]
            )
            ->willReturnOnConsecutiveCalls(
                $parentHashes,
                $hashes
            );
        $reducer->expects($this->once())
                ->method('mapEntityHashes')
                ->with($parentHashes)
                ->willReturn($mappedParentHashes);
        $reducer->expects($this->exactly(4))
            ->method('fetchEntityFromHash')
            ->withConsecutive(
                ['abc'],
                ['ghi'],
                ['def'],
                ['jkl']
            )
            ->willReturnOnConsecutiveCalls(
                $entity1,
                $entity2,
                $parentEntity2,
                $entity3
            );
        $reducer->expects($this->once())
                ->method('reduceEntity')
                ->with($this->equalTo($entity2), $parentEntity2);
        $reducer->expects($this->once())
                ->method('setHashesToCombination')
                ->with($combination, $expectedHashes);

        $this->invokeMethod($reducer, 'reduce', $combination, $parentCombination);
    }

    /**
     * Tests the mapEntityHashes method.
     * @throws ReflectionException
     * @covers ::mapEntityHashes
     */
    public function testMapEntityHashes(): void
    {
        $hashes = ['abc', 'def'];

        /* @var EntityWithIdentifierInterface|MockObject $entity1 */
        $entity1 = $this->getMockBuilder(EntityWithIdentifierInterface::class)
                        ->setMethods(['getIdentifier'])
                        ->getMockForAbstractClass();
        $entity1->expects($this->once())
                ->method('getIdentifier')
                ->willReturn('ghi');

        /* @var EntityWithIdentifierInterface|MockObject $entity2 */
        $entity2 = $this->getMockBuilder(EntityWithIdentifierInterface::class)
                        ->setMethods(['getIdentifier'])
                        ->getMockForAbstractClass();
        $entity2->expects($this->once())
                ->method('getIdentifier')
                ->willReturn('jkl');

        $expectedResult = ['ghi' => 'abc', 'jkl' => 'def'];

        /* @var AbstractIdentifiedEntityReducer|MockObject $reducer */
        $reducer = $this->getMockBuilder(AbstractIdentifiedEntityReducer::class)
                        ->setMethods(['fetchEntityFromHash'])
                        ->disableOriginalConstructor()
                        ->getMockForAbstractClass();
        $reducer->expects($this->exactly(2))
                ->method('fetchEntityFromHash')
                ->withConsecutive(
                    ['abc'],
                    ['def']
                )
                ->willReturnOnConsecutiveCalls(
                    $entity1,
                    $entity2
                );

        $result = $this->invokeMethod($reducer, 'mapEntityHashes', $hashes);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Provides the data for the fetchEntityFromHash test.
     * @return array
     */
    public function provideFetchEntityFromHash(): array
    {
        return [
            [$this->createMock(EntityWithIdentifierInterface::class), false],
            [null, true],
        ];
    }

    /**
     * Tests the fetchEntityFromHash method.
     * @param EntityWithIdentifierInterface|null $resultGet
     * @param bool $expectException
     * @throws ReflectionException
     * @covers ::fetchEntityFromHash
     * @dataProvider provideFetchEntityFromHash
     */
    public function testFetchEntityFromHash(?EntityWithIdentifierInterface $resultGet, bool $expectException): void
    {
        $hash = 'abc';

        /* @var EntityRegistry|MockObject $rawEntityRegistry */
        $rawEntityRegistry = $this->getMockBuilder(EntityRegistry::class)
                                  ->setMethods(['get'])
                                  ->disableOriginalConstructor()
                                  ->getMock();
        $rawEntityRegistry->expects($this->once())
                          ->method('get')
                          ->with($hash)
                          ->willReturn($resultGet);

        /* @var EntityRegistry $reducedEntityRegistry */
        $reducedEntityRegistry = $this->createMock(EntityRegistry::class);

        /* @var AbstractIdentifiedEntityReducer|MockObject $reducer */
        $reducer = $this->getMockBuilder(AbstractIdentifiedEntityReducer::class)
                        ->setConstructorArgs([$rawEntityRegistry, $reducedEntityRegistry])
                        ->getMockForAbstractClass();

        if ($expectException) {
            $this->expectException(ReducerException::class);
        }

        $result = $this->invokeMethod($reducer, 'fetchEntityFromHash', $hash);
        $this->assertSame($resultGet, $result);
    }
}
