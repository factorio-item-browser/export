<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\OutputProcessor\DumpProcessor;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\Entity\Dump\Recipe;
use FactorioItemBrowser\Export\OutputProcessor\DumpProcessor\ExpensiveRecipeDumpProcessor;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the ExpensiveRecipeDumpProcessor class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\OutputProcessor\DumpProcessor\ExpensiveRecipeDumpProcessor
 */
class ExpensiveRecipeDumpProcessorTest extends TestCase
{
    use ReflectionTrait;

    /** @var SerializerInterface&MockObject */
    private SerializerInterface $exportSerializer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->exportSerializer = $this->createMock(SerializerInterface::class);
    }

    /**
     * @throws ReflectionException
     * @covers ::__construct
     * @covers ::getType
     */
    public function testConstruct(): void
    {
        $instance = new ExpensiveRecipeDumpProcessor($this->exportSerializer);

        $this->assertSame($this->exportSerializer, $this->extractProperty($instance, 'exportSerializer'));
        $this->assertSame('expensive-recipe', $instance->getType());
    }

    /**
     * @covers ::process
     */
    public function testProcess(): void
    {
        $serializedDump = 'abc';
        $recipe1 = $this->createMock(Recipe::class);
        $recipe2 = $this->createMock(Recipe::class);

        $dump = new Dump();
        $dump->expensiveRecipes = [$recipe1];

        $this->exportSerializer->expects($this->once())
                               ->method('deserialize')
                               ->with(
                                   $this->identicalTo($serializedDump),
                                   $this->identicalTo(Recipe::class),
                                   $this->identicalTo('json'),
                               )
                               ->willReturn($recipe2);

        $instance = new ExpensiveRecipeDumpProcessor($this->exportSerializer);
        $instance->process($serializedDump, $dump);

        $this->assertContains($recipe1, $dump->expensiveRecipes);
        $this->assertContains($recipe2, $dump->expensiveRecipes);
    }
}
