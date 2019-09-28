<?php

declare(strict_types=1);

namespace FactorioItemBrowserTestSerializer\Export\Entity\Dump;

use FactorioItemBrowser\Export\Entity\Dump\Product;
use FactorioItemBrowserTestAsset\Export\SerializerTestCase;

/**
 * The PHPUnit test of serializing the Product class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversNothing
 */
class ProductTest extends SerializerTestCase
{
    /**
     * Returns the object to be serialized or deserialized.
     * @return object
     */
    protected function getObject(): object
    {
        $result = new Product();
        $result->setType('ghi')
               ->setName('jkl')
               ->setAmountMin(12.34)
               ->setAmountMax(23.45)
               ->setProbability(34.56);
        return $result;
    }

    /**
     * Returns the serialized data.
     * @return array
     */
    protected function getData(): array
    {
        return [
            'type' => 'ghi',
            'name' => 'jkl',
            'amount_min' => 12.34,
            'amount_max' => 23.45,
            'probability' => 34.56,
        ];
    }
}
