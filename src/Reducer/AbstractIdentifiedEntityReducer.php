<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Reducer;

use FactorioItemBrowser\Export\Exception\ReducerException;
use FactorioItemBrowser\ExportData\Entity\EntityInterface;
use FactorioItemBrowser\ExportData\Entity\EntityWithIdentifierInterface;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;
use FactorioItemBrowser\ExportData\Registry\EntityRegistry;

/**
 * The abstract class of reducers managing identifiable entities.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
abstract class AbstractIdentifiedEntityReducer implements ReducerInterface
{
    /**
     * The registry of the raw entities.
     * @var EntityRegistry
     */
    protected $rawEntityRegistry;

    /**
     * The registry of the reduced entities.
     * @var EntityRegistry
     */
    protected $reducedEntityRegistry;

    /**
     * Initializes the reducer.
     * @param EntityRegistry $rawEntityRegistry
     * @param EntityRegistry $reducedEntityRegistry
     */
    public function __construct(EntityRegistry $rawEntityRegistry, EntityRegistry $reducedEntityRegistry)
    {
        $this->rawEntityRegistry = $rawEntityRegistry;
        $this->reducedEntityRegistry = $reducedEntityRegistry;
    }

    /**
     * Reduces the combination against the parent combination.
     * @param Combination $combination
     * @param Combination $parentCombination
     * @throws ReducerException
     */
    public function reduce(Combination $combination, Combination $parentCombination): void
    {
        $parentHashes = $this->mapEntityHashes($this->getHashesFromCombination($parentCombination));

        $reducedHashes = [];
        foreach ($this->getHashesFromCombination($combination) as $hash) {
            $entity = $this->fetchEntityFromHash($hash);
            $identifier = $entity->getIdentifier();

            $parentEntityHash = $parentHashes[$identifier] ?? null;
            if ($parentEntityHash === null) {
                $reducedHashes[] = $this->reducedEntityRegistry->set($entity);
            } elseif ($parentEntityHash !== $hash) {
                $reducedEntity = clone($entity);
                $this->reduceEntity($reducedEntity, $this->fetchEntityFromHash($parentEntityHash));
                $reducedHashes[] = $this->reducedEntityRegistry->set($reducedEntity);
            }
        }
        $this->setHashesToCombination($combination, $reducedHashes);
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
     * @return array|string[]
     * @throws ReducerException
     */
    protected function mapEntityHashes(array $hashes): array
    {
        $result = [];
        foreach ($hashes as $hash) {
            $entity = $this->fetchEntityFromHash($hash);
            $result[$entity->getIdentifier()] = $hash;
        }
        return $result;
    }

    /**
     * Fetches the entity with the specified hash.
     * @param string $hash
     * @return EntityWithIdentifierInterface
     * @throws ReducerException
     */
    protected function fetchEntityFromHash(string $hash): EntityWithIdentifierInterface
    {
        $result = $this->rawEntityRegistry->get($hash);
        if (!$result instanceof EntityWithIdentifierInterface) {
            throw new ReducerException('Cannot find entity with hash #' . $hash);
        }
        return $result;
    }

    /**
     * Reduces the entity against its parent.
     * @param EntityInterface $entity
     * @param EntityInterface $parentEntity
     */
    abstract protected function reduceEntity(EntityInterface $entity, EntityInterface $parentEntity): void;

    /**
     * Sets the hashes to the combination.
     * @param Combination $combination
     * @param array|string[] $hashes
     */
    abstract protected function setHashesToCombination(Combination $combination, array $hashes): void;
}
