<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\OutputProcessor\DumpProcessor;

use BluePsyduck\LaminasAutoWireFactory\Attribute\Alias;
use FactorioItemBrowser\Export\Constant\ServiceName;
use FactorioItemBrowser\Export\Entity\Dump\Dump;
use FactorioItemBrowser\Export\Entity\Dump\Recipe;
use JMS\Serializer\SerializerInterface;

/**
 * The dump processor for the expensive recipes.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ExpensiveRecipeDumpProcessor implements DumpProcessorInterface
{
    public function __construct(
        #[Alias(ServiceName::SERIALIZER)]
        private readonly SerializerInterface $serializer,
    ) {
    }

    public function getType(): string
    {
        return 'expensive-recipe';
    }

    public function process(string $serializedDump, Dump $dump): void
    {
        $dump->expensiveRecipes[] = $this->serializer->deserialize($serializedDump, Recipe::class, 'json');
    }
}
