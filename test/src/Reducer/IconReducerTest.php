<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Reducer;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Export\Exception\ReducerException;
use FactorioItemBrowser\Export\Reducer\IconReducer;
use FactorioItemBrowser\ExportData\Entity\Icon;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;
use FactorioItemBrowser\ExportData\Registry\EntityRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the IconReducer class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Reducer\IconReducer
 */
class IconReducerTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        /* @var EntityRegistry $rawIconRegistry */
        $rawIconRegistry = $this->createMock(EntityRegistry::class);
        /* @var EntityRegistry $reducedIconRegistry */
        $reducedIconRegistry = $this->createMock(EntityRegistry::class);

        $reducer = new IconReducer($rawIconRegistry, $reducedIconRegistry);

        $this->assertSame($rawIconRegistry, $this->extractProperty($reducer, 'rawIconRegistry'));
        $this->assertSame($reducedIconRegistry, $this->extractProperty($reducer, 'reducedIconRegistry'));
    }

    /**
     * Tests the reduce method.
     * @throws ReducerException
     * @throws ReflectionException
     * @covers ::reduce
     */
    public function testReduce(): void
    {
        $parentCombination = new Combination();
        $parentCombination->setIconHashes(['abc', 'def']);
        $expectedIconHashes = ['ghi'];

        /* @var Combination|MockObject $combination */
        $combination = $this->getMockBuilder(Combination::class)
                            ->setMethods(['setIconHashes'])
                            ->getMock();
        $combination->expects($this->once())
                    ->method('setIconHashes')
                    ->with($expectedIconHashes);
        $this->injectProperty($combination, 'iconHashes', ['abc', 'ghi']);

        /* @var EntityRegistry $rawIconRegistry */
        $rawIconRegistry = $this->createMock(EntityRegistry::class);
        /* @var EntityRegistry $reducedIconRegistry */
        $reducedIconRegistry = $this->createMock(EntityRegistry::class);

        $reducer = new IconReducer($rawIconRegistry, $reducedIconRegistry);

        $reducer->reduce($combination, $parentCombination);
    }

    /**
     * Provides the data for the persist test.
     * @return array
     */
    public function providePersist(): array
    {
        return [
            [false],
            [true],
        ];
    }

    /**
     * Tests the persist method.
     * @param bool $withException
     * @throws ReducerException
     * @covers ::persist
     * @dataProvider providePersist
     */
    public function testPersist(bool $withException): void
    {
        $iconHashes = ['abc', 'def'];
        $icon1 = (new Icon())->setSize(42);
        $icon2 = (new Icon())->setSize(21);
        $combination = new Combination();
        $combination->setIconHashes($iconHashes);

        /* @var EntityRegistry|MockObject $rawIconRegistry */
        $rawIconRegistry = $this->getMockBuilder(EntityRegistry::class)
                                ->setMethods(['get'])
                                ->disableOriginalConstructor()
                                ->getMock();
        if ($withException) {
            $rawIconRegistry->expects($this->once())
                            ->method('get')
                            ->with('abc')
                            ->willReturn(null);
        } else {
            $rawIconRegistry->expects($this->exactly(2))
                            ->method('get')
                            ->withConsecutive(
                                ['abc'],
                                ['def']
                            )
                            ->willReturnOnConsecutiveCalls(
                                $icon1,
                                $icon2
                            );
        }

        /* @var EntityRegistry|MockObject $reducedIconRegistry */
        $reducedIconRegistry = $this->getMockBuilder(EntityRegistry::class)
                                    ->setMethods(['set'])
                                    ->disableOriginalConstructor()
                                    ->getMock();
        $reducedIconRegistry->expects($withException ? $this->never() : $this->exactly(2))
                            ->method('set')
                            ->withConsecutive(
                                [$icon1],
                                [$icon2]
                            );

        if ($withException) {
            $this->expectException(ReducerException::class);
        }

        $reducer = new IconReducer($rawIconRegistry, $reducedIconRegistry);
        $reducer->persist($combination);
    }
}
