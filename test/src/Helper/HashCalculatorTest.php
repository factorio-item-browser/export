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

    /**
     * The mocked serializer.
     * @var SerializerInterface&MockObject
     */
    protected $serializer;

    /**
     * Sets up the test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->serializer = $this->createMock(SerializerInterface::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $helper = new HashCalculator($this->serializer);

        $this->assertSame($this->serializer, $this->extractProperty($helper, 'serializer'));
    }

    /**
     * Tests the hashIcon method.
     * @covers ::hashIcon
     */
    public function testHashIcon(): void
    {
        $hash = 'foo';

        /* @var Layer&MockObject $layer */
        $layer = $this->createMock(Layer::class);

        $icon = new Icon();
        $icon->setId('abc')
             ->setSize(42)
             ->setRenderedSize(21)
             ->addLayer($layer);

        $expectedIcon = new Icon();
        $expectedIcon->setSize(42)
                     ->addLayer($layer);

        /* @var HashCalculator&MockObject $helper */
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
     * Tests the hashRecipe method.
     * @covers ::hashRecipe
     */
    public function testHashRecipe(): void
    {
        $hash = 'foo';

        /* @var Layer&MockObject $layer */
        $layer = $this->createMock(Layer::class);

        $recipe = new Recipe();
        $recipe->setName('abc')
               ->setMode('def')
               ->setCraftingCategory('ghi')
               ->setCraftingTime(13.37)
               ->setIconId('jkl');

        $expectedRecipe = new Recipe();
        $expectedRecipe->setCraftingCategory('ghi')
                       ->setCraftingTime(13.37);

        /* @var HashCalculator&MockObject $helper */
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
     * Tests the hashEntity method.
     * @throws ReflectionException
     * @covers ::hashEntity
     */
    public function testHashEntity(): void
    {
        $serializedEntity = 'abc';
        $expectedResult = '90015098-3cd2-4fb0-d696-3f7d28e17f72';

        /* @var stdClass&MockObject $entity */
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
