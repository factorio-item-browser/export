<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Merger;

use FactorioItemBrowser\Export\Exception\MergerException;
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
        $destinationEntities = $this->mapEntitiesToIdentifier($this->getHashesFromCombination($destination));

        $mergedHashes = [];
        foreach ($this->getHashesFromCombination($source) as $hash) {
            $sourceEntity = $this->fetchEntityFromHash($hash);
            $identifier = $sourceEntity->getIdentifier();

            if (isset($destinationEntities[$identifier])) {
                $mergedEntity = clone($destinationEntities[$identifier]);
                $this->mergeEntities($mergedEntity, $sourceEntity);
                $mergedHashes[] = $this->entityRegistry->set($mergedEntity);
            } else {
                $mergedHashes[] = $hash;
            }
        }
        $this->setHashesToCombination($destination, $mergedHashes);
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
     * @param EntityWithIdentifierInterface $destination
     * @param EntityWithIdentifierInterface $source
     */
    abstract protected function mergeEntities(
        EntityWithIdentifierInterface $destination,
        EntityWithIdentifierInterface $source
    ): void;

    /**
     * Sets the hashes to the combination.
     * @param Combination $combination
     * @param array|string[] $hashes
     */
    abstract protected function setHashesToCombination(Combination $combination, array $hashes): void;
}
