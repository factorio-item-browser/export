<?php

namespace FactorioItemBrowserTest\Export\Merger;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Export\Exception\MergerException;
use FactorioItemBrowser\Export\Merger\AbstractIdentifiedEntityMerger;
use FactorioItemBrowser\ExportData\Entity\EntityWithIdentifierInterface;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;
use FactorioItemBrowser\ExportData\Registry\EntityRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the AbstractIdentifiedEntityMerger class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Merger\AbstractIdentifiedEntityMerger
 */
class AbstractIdentifiedEntityMergerTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests the constructing.
     * @covers ::__construct
     * @throws ReflectionException
     */
    public function testConstruct(): void
    {
        /* @var EntityRegistry $entityRegistry */
        $entityRegistry = $this->createMock(EntityRegistry::class);

        /* @var AbstractIdentifiedEntityMerger|MockObject $merger */
        $merger = $this->getMockBuilder(AbstractIdentifiedEntityMerger::class)
                       ->setConstructorArgs([$entityRegistry])
                       ->getMockForAbstractClass();

        $this->assertSame($entityRegistry, $this->extractProperty($merger, 'entityRegistry'));
    }

    /**
     * Tests the merge method.
     * @throws MergerException
     * @covers ::merge
     */
    public function testMerge(): void
    {
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

        /* @var EntityWithIdentifierInterface $destinationEntity1 */
        $destinationEntity1 = $this->createMock(EntityWithIdentifierInterface::class);

        $destinationHashes = ['abc'];
        $mappedDestinationHashes = ['foo' => $destinationEntity1];
        $sourceHashes = ['abc', 'def'];
        $expectedHashes = ['abc', 'def'];

        /* @var Combination $destination */
        $destination = $this->createMock(Combination::class);
        /* @var Combination $source */
        $source = $this->createMock(Combination::class);

        /* @var EntityRegistry|MockObject $entityRegistry */
        $entityRegistry = $this->getMockBuilder(EntityRegistry::class)
                               ->setMethods(['set'])
                               ->disableOriginalConstructor()
                               ->getMock();
        $entityRegistry->expects($this->once())
                       ->method('set')
                       ->with($this->equalTo($destinationEntity1))
                       ->willReturn('abc');

        /* @var AbstractIdentifiedEntityMerger|MockObject $merger */
        $merger = $this->getMockBuilder(AbstractIdentifiedEntityMerger::class)
                       ->setMethods([
                           'getHashesFromCombination',
                           'mapEntitiesToIdentifier',
                           'fetchEntityFromHash',
                           'mergeEntities',
                           'setHashesToCombination',
                       ])
                       ->setConstructorArgs([$entityRegistry])
                       ->getMockForAbstractClass();

        $merger->expects($this->exactly(2))
               ->method('getHashesFromCombination')
               ->withConsecutive(
                   [$destination],
                   [$source]
               )
               ->willReturnOnConsecutiveCalls(
                   $destinationHashes,
                   $sourceHashes
               );
        $merger->expects($this->once())
               ->method('mapEntitiesToIdentifier')
               ->with($destinationHashes)
               ->willReturn($mappedDestinationHashes);
        $merger->expects($this->exactly(2))
               ->method('fetchEntityFromHash')
               ->withConsecutive(
                   ['abc'],
                   ['def']
               )
               ->willReturnOnConsecutiveCalls(
                   $entity1,
                   $entity2
               );
        $merger->expects($this->once())
               ->method('mergeEntities')
               ->with($this->equalTo($destinationEntity1), $entity1);
        $merger->expects($this->once())
               ->method('setHashesToCombination')
               ->with($destination, $expectedHashes);

        $merger->merge($destination, $source);
    }


    /**
     * Tests the mapEntitiesToIdentifier method.
     * @throws ReflectionException
     * @covers ::mapEntitiesToIdentifier
     */
    public function testMapEntitiesToIdentifier(): void
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

        $expectedResult = [
            'ghi' => $entity1,
            'jkl' => $entity2,
        ];

        /* @var AbstractIdentifiedEntityMerger|MockObject $merger */
        $merger = $this->getMockBuilder(AbstractIdentifiedEntityMerger::class)
                       ->setMethods(['fetchEntityFromHash'])
                       ->disableOriginalConstructor()
                       ->getMockForAbstractClass();
        $merger->expects($this->exactly(2))
            ->method('fetchEntityFromHash')
            ->withConsecutive(
                ['abc'],
                ['def']
            )
            ->willReturnOnConsecutiveCalls(
                $entity1,
                $entity2
            );

        $result = $this->invokeMethod($merger, 'mapEntitiesToIdentifier', $hashes);
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

        /* @var EntityRegistry|MockObject $entityRegistry */
        $entityRegistry = $this->getMockBuilder(EntityRegistry::class)
                               ->setMethods(['get'])
                               ->disableOriginalConstructor()
                               ->getMock();
        $entityRegistry->expects($this->once())
                       ->method('get')
                       ->with($hash)
                       ->willReturn($resultGet);

        /* @var AbstractIdentifiedEntityMerger|MockObject $reducer */
        $reducer = $this->getMockBuilder(AbstractIdentifiedEntityMerger::class)
                        ->setConstructorArgs([$entityRegistry])
                        ->getMockForAbstractClass();

        if ($expectException) {
            $this->expectException(MergerException::class);
        }

        $result = $this->invokeMethod($reducer, 'fetchEntityFromHash', $hash);
        $this->assertSame($resultGet, $result);
    }
}
