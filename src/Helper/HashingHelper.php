<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Helper;

use FactorioItemBrowser\ExportData\Entity\Icon;
use FactorioItemBrowser\ExportData\Entity\Recipe;
use JMS\Serializer\SerializerInterface;

/**
 * The helper class for hashing some entities.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class HashingHelper
{
    /**
     * The serializer.
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * Initializes the helper.
     * @param SerializerInterface $exportDataSerializer
     */
    public function __construct(SerializerInterface $exportDataSerializer)
    {
        $this->serializer = $exportDataSerializer;
    }

    /**
     * Hashes the specified icon.
     * @param Icon $icon
     * @return string
     */
    public function hashIcon(Icon $icon): string
    {
        $icon = clone($icon);
        $icon->setHash('')
             ->setRenderedSize(0);

        return $this->hashEntity($icon);
    }

    /**
     * Hashes the specified recipe.
     * @param Recipe $recipe
     * @return string
     */
    public function hashRecipe(Recipe $recipe): string
    {
        $recipe = clone($recipe);
        $recipe->setName('')
               ->setMode('')
               ->setIconHash('');

        return $this->hashEntity($recipe);
    }

    /**
     * Hashes the specified entity.
     * @param object $entity
     * @return string
     */
    protected function hashEntity(object $entity): string
    {
        return substr(hash('md5', $this->serializer->serialize($entity, 'json')), 0, 16);
    }
}
