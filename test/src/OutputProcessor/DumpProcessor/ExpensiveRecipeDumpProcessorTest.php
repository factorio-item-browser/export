<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\OutputProcessor\DumpProcessor;

use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\Entity\Dump\Recipe;
use FactorioItemBrowser\Export\OutputProcessor\DumpProcessor\ExpensiveRecipeDumpProcessor;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the ExpensiveRecipeDumpProcessor class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Export\OutputProcessor\DumpProcessor\ExpensiveRecipeDumpProcessor
 */
class ExpensiveRecipeDumpProcessorTest extends TestCase
{
    /** @var SerializerInterface&MockObject */
    private SerializerInterface $serializer;

    protected function setUp(): void
    {
        $this->serializer = $this->createMock(SerializerInterface::class);
    }

    private function createInstance(): ExpensiveRecipeDumpProcessor
    {
        return new ExpensiveRecipeDumpProcessor(
            $this->serializer,
        );
    }

    public function testGetType(): void
    {
        $expectedResult = 'expensive-recipe';

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
        $dump->expensiveRecipes = [$recipe1];

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

        $this->assertContains($recipe1, $dump->expensiveRecipes);
        $this->assertContains($recipe2, $dump->expensiveRecipes);
    }
}
