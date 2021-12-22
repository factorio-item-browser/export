<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\OutputProcessor\DumpProcessor;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\Entity\Dump\Recipe;
use FactorioItemBrowser\Export\OutputProcessor\DumpProcessor\NormalRecipeDumpProcessor;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the NormalRecipeDumpProcessor class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Export\OutputProcessor\DumpProcessor\NormalRecipeDumpProcessor
 */
class NormalRecipeDumpProcessorTest extends TestCase
{
    /** @var SerializerInterface&MockObject */
    private SerializerInterface $serializer;

    protected function setUp(): void
    {
        $this->serializer = $this->createMock(SerializerInterface::class);
    }

    private function createInstance(): NormalRecipeDumpProcessor
    {
        return new NormalRecipeDumpProcessor(
            $this->serializer,
        );
    }

    public function testGetType(): void
    {
        $expectedResult = 'normal-recipe';

        $instance = $this->createInstance();
        $result = $instance->getType();

        $this->assertSame($expectedResult, $result);
    }

    public function testProcess(): void
    {
        $serializedDump = 'abc';
        $recipe1 = $this->createMock(Recipe::class);
        $recipe2 = $this->createMock(Recipe::class);

        $dump = new Dump();
        $dump->normalRecipes = [$recipe1];

        $this->serializer->expects($this->once())
                         ->method('deserialize')
                         ->with(
                             $this->identicalTo($serializedDump),
                             $this->identicalTo(Recipe::class),
                             $this->identicalTo('json'),
                         )
                         ->willReturn($recipe2);

        $instance = $this->createInstance();
        $instance->process($serializedDump, $dump);

        $this->assertContains($recipe1, $dump->normalRecipes);
        $this->assertContains($recipe2, $dump->normalRecipes);
    }
}
