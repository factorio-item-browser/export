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
 * @coversDefaultClass \FactorioItemBrowser\Export\Helper\HashCalculator
 */
class HashCalculatorTest extends TestCase
{
    use ReflectionTrait;

    /** @var SerializerInterface&MockObject */
    private $serializer;

    protected function setUp(): void
    {
        $this->serializer = $this->createMock(SerializerInterface::class);
    }

    /**
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $helper = new HashCalculator($this->serializer);

        $this->assertSame($this->serializer, $this->extractProperty($helper, 'serializer'));
    }

    /**
     * @covers ::hashIcon
     */
    public function testHashIcon(): void
    {
        $hash = 'foo';

        $layer = $this->createMock(Layer::class);

        $icon = new Icon();
        $icon->id = 'abc';
        $icon->size = 42;
        $icon->layers[] = $layer;

        $expectedIcon = new Icon();
        $expectedIcon->size = 42;
        $expectedIcon->layers[] = $layer;

        $helper = $this->getMockBuilder(HashCalculator::class)
                       ->onlyMethods(['hashEntity'])
                       ->setConstructorArgs([$this->serializer])
                       ->getMock();
        $helper->expects($this->once())
               ->method('hashEntity')
               ->with($this->equalTo($expectedIcon))
               ->willReturn($hash);

        $result = $helper->hashIcon($icon);

        $this->assertSame($hash, $result);
    }

    /**
     * @covers ::hashRecipe
     */
    public function testHashRecipe(): void
    {
        $hash = 'foo';

        $recipe = new Recipe();
        $recipe->name = 'abc';
        $recipe->mode = 'def';
        $recipe->craftingCategory = 'ghi';
        $recipe->craftingTime = 13.37;
        $recipe->iconId = 'jkl';

        $expectedRecipe = new Recipe();
        $expectedRecipe->craftingCategory = 'ghi';
        $expectedRecipe->craftingTime = 13.37;

        $helper = $this->getMockBuilder(HashCalculator::class)
                       ->onlyMethods(['hashEntity'])
                       ->setConstructorArgs([$this->serializer])
                       ->getMock();
        $helper->expects($this->once())
               ->method('hashEntity')
               ->with($this->equalTo($expectedRecipe))
               ->willReturn($hash);

        $result = $helper->hashRecipe($recipe);

        $this->assertSame($hash, $result);
    }

    /**
     * @throws ReflectionException
     * @covers ::hashEntity
     */
    public function testHashEntity(): void
    {
        $serializedEntity = 'abc';
        $expectedResult = '90015098-3cd2-4fb0-d696-3f7d28e17f72';

        $entity = $this->createMock(stdClass::class);

        $this->serializer->expects($this->once())
                         ->method('serialize')
                         ->with($this->identicalTo($entity), $this->identicalTo('json'))
                         ->willReturn($serializedEntity);

        $helper = new HashCalculator($this->serializer);
        $result = $this->invokeMethod($helper, 'hashEntity', $entity);

        $this->assertSame($expectedResult, $result);
    }
}
