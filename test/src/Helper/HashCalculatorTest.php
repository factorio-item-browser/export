<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Helper;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Export\Helper\HashCalculator;
use FactorioItemBrowser\ExportData\Entity\Icon;
use FactorioItemBrowser\ExportData\Entity\Icon\Layer;
use FactorioItemBrowser\ExportData\Entity\Recipe;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use stdClass;

/**
 * The PHPUnit test of the HashCalculator class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Export\Helper\HashCalculator
 */
class HashCalculatorTest extends TestCase
{
    use ReflectionTrait;

    /** @var SerializerInterface&MockObject */
    private SerializerInterface $serializer;

    protected function setUp(): void
    {
        $this->serializer = $this->createMock(SerializerInterface::class);
    }

    /**
     * @param array<string> $methods
     * @return HashCalculator&MockObject
     */
    private function createInstance(array $methods = []): HashCalculator
    {
        return $this->getMockBuilder(HashCalculator::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($methods)
                    ->setConstructorArgs([
                        $this->serializer,
                    ])
                    ->getMock();
    }

    public function testHashIcon(): void
    {
        $serializedEntity = 'abc';
        $expectedResult = '90015098-3cd2-4fb0-d696-3f7d28e17f72';
        $layer = $this->createMock(Layer::class);

        $icon = new Icon();
        $icon->id = 'abc';
        $icon->size = 42;
        $icon->layers[] = $layer;

        $expectedIcon = new Icon();
        $expectedIcon->size = 42;
        $expectedIcon->layers[] = $layer;

        $this->serializer->expects($this->once())
                         ->method('serialize')
                         ->with($this->equalTo($expectedIcon), $this->identicalTo('json'))
                         ->willReturn($serializedEntity);

        $instance = $this->createInstance();
        $result = $instance->hashIcon($icon);

        $this->assertSame($expectedResult, $result);
    }

    public function testHashRecipe(): void
    {
        $serializedEntity = 'abc';
        $expectedResult = '90015098-3cd2-4fb0-d696-3f7d28e17f72';

        $recipe = new Recipe();
        $recipe->name = 'abc';
        $recipe->mode = 'def';
        $recipe->category = 'ghi';
        $recipe->time = 13.37;
        $recipe->iconId = 'jkl';

        $expectedRecipe = new Recipe();
        $expectedRecipe->name = 'abc';
        $expectedRecipe->category = 'ghi';
        $expectedRecipe->time = 13.37;

        $this->serializer->expects($this->once())
                         ->method('serialize')
                         ->with($this->equalTo($expectedRecipe), $this->identicalTo('json'))
                         ->willReturn($serializedEntity);

        $instance = $this->createInstance();
        $result = $instance->hashRecipe($recipe);

        $this->assertSame($expectedResult, $result);
    }
}
