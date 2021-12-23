<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\OutputProcessor\DumpProcessor;

use BluePsyduck\LaminasAutoWireFactory\Attribute\Alias;
use FactorioItemBrowser\Export\Constant\ServiceName;
use FactorioItemBrowser\Export\Entity\Dump\Dump;
use JMS\Serializer\SerializerInterface;

/**
 * The abstract class of the dump processors using the serializer.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 *
 * @template TEntity of object
 */
abstract class AbstractSerializerDumpProcessor implements DumpProcessorInterface
{
    public function __construct(
        #[Alias(ServiceName::SERIALIZER)]
        private readonly SerializerInterface $serializer,
    ) {
    }

    public function process(string $serializedDump, Dump $dump): void
    {
        /** @var TEntity $entity */
        $entity = $this->serializer->deserialize($serializedDump, $this->getEntityClass(), 'json');
        $this->addEntityToDump($entity, $dump);
    }

    /**
     * Returns the entity class used by this dump processor.
     * @return class-string<TEntity>
     */
    abstract protected function getEntityClass(): string;

    /**
     * Adds the entity to the dump structure.
     * @param TEntity $entity
     * @param Dump $dump
     * @return void
     */
    abstract protected function addEntityToDump(object $entity, Dump $dump): void;
}
