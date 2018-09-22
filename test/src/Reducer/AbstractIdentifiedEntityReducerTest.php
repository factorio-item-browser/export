<?php

namespace FactorioItemBrowserTest\Export\Reducer;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Export\Exception\ReducerException;
use FactorioItemBrowser\Export\Reducer\AbstractIdentifiedEntityReducer;
use FactorioItemBrowser\ExportData\Entity\EntityWithIdentifierInterface;
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
