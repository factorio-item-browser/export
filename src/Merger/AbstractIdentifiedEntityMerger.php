<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Merger;

use FactorioItemBrowser\Export\Exception\MergerException;
use FactorioItemBrowser\ExportData\Entity\EntityInterface;
use FactorioItemBrowser\ExportData\Entity\EntityWithIdentifierInterface;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;
use FactorioItemBrowser\ExportData\Registry\EntityRegistry;

/**
 * The abstract class of the mergers based on identified entities.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
abstract class AbstractIdentifiedEntityMerger implements MergerInterface
{
    /**
     * The registry of the entities.
     * @var EntityRegistry
     */
    protected $entityRegistry;

    /**
     * Initializes the merger.
     * @param EntityRegistry $entityRegistry
     */
    public function __construct(EntityRegistry $entityRegistry)
    {
        $this->entityRegistry = $entityRegistry;
    }

    /**
     * Merges the source combination into the destination one.
     * @param Combination $destination
     * @param Combination $source
     * @throws MergerException
     */
    public function merge(Combination $destination, Combination $source): void
    {
        $mergedEntities = $this->mapEntitiesToIdentifier($this->getHashesFromCombination($destination));

        foreach ($this->getHashesFromCombination($source) as $hash) {
            $sourceEntity = $this->fetchEntityFromHash($hash);
            $identifier = $sourceEntity->getIdentifier();

            if (isset($mergedEntities[$identifier])) {
                $mergedEntity = clone($mergedEntities[$identifier]);
                $this->mergeEntity($mergedEntity, $sourceEntity);
            } else {
                $mergedEntity = $sourceEntity;
            }
            $mergedEntities[$identifier] = $mergedEntity;
        }

        $this->setHashesToCombination($destination, $this->getHashesForEntities($mergedEntities));
    }

    /**
     * Returns the hashes to use from the specified combination.
     * @param Combination $combination
     * @return array|string[]
     */
    abstract protected function getHashesFromCombination(Combination $combination): array;

    /**
     * Maps the entity hashes to their instances.
     * @param array|string[] $hashes
     * @return array|EntityWithIdentifierInterface[]
     * @throws MergerException
     */
    protected function mapEntitiesToIdentifier(array $hashes): array
    {
        $result = [];
        foreach ($hashes as $hash) {
            $entity = $this->fetchEntityFromHash($hash);
            $result[$entity->getIdentifier()] = $entity;
        }
        return $result;
    }

    /**
     * Fetches the entity with the specified hash.
     * @param string $hash
     * @return EntityWithIdentifierInterface
     * @throws MergerException
     */
    protected function fetchEntityFromHash(string $hash): EntityWithIdentifierInterface
    {
        $result = $this->entityRegistry->get($hash);
        if (!$result instanceof EntityWithIdentifierInterface) {
            throw new MergerException('Cannot find entity with hash #' . $hash);
        }
        return $result;
    }

    /**
     * Merges the source entity into the destination one.
     * @param EntityInterface $destination
     * @param EntityInterface $source
     */
    abstract protected function mergeEntity(EntityInterface $destination, EntityInterface $source): void;

    /**
     * Returns the hashes of the specified entities.
     * @param array|EntityInterface[] $entities
     * @return array|string[]
     */
    protected function getHashesForEntities(array $entities): array
    {
        $result = [];
        foreach ($entities as $entity) {
            $result[] = $this->entityRegistry->set($entity);
        }
        return $result;
    }

    /**
     * Sets the hashes to the combination.
     * @param Combination $combination
     * @param array|string[] $hashes
     */
    abstract protected function setHashesToCombination(Combination $combination, array $hashes): void;
}
